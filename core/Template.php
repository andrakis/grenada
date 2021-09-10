<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

define('TEMPLATES', BASE.'content/templates/');

/**
 * Description of Template
 *
 * @author Daedalus
 */
class Template implements Processable {
    /**
     * If enabled, throws an exception if a given variable is not found
     * @var boolean
     */
    static public $strictMode = false;
    
    /** Flags for use in Template::processVariable */

    /** No flags enabled */
    const TF_NONE = 0;

    /**
     * Disable plain text expansion (eg, :1 return an int, :"string" returns a string)
     * If disabled, only variables may be used
     */
    const TF_NOTPLAINTEXT = 1;

    /**
     * Variable is not required.
     * If strict mode is on, disables throwing an exception if the variable is not found
     */
    const TF_VARIABLENOTREQUIRED = 2;

    private $templateString;
    private $templateVariables = array();

    static $constructorHooks = array();
    /**
     * Adds a hook event that is called on each Template's __construct event
     * @param string $fnName Name of the function to call
     */
    static public function addConstructorHook($fnName)
    {
        if (!function_exists($fnName)) throw new HookNotFoundException($fnName);

        self::$constructorHooks[] = $fnName;
    }

    /**
     * Template stack.
     * The stack grows and shrinks as different flow control operations are reached.
     * Template execution will only occur if the current stack value is true.
     * @var array(bool)
     */
    private $templateStack = array(true);

    /**
     * The current Template running a process() call
     * @var Template
     */
    static public $currentProcessor = null;
    /**
     * Stores deferred tokens (such as escaped braces \{ \}) to remove them from
     * the token processor, but still include in the output.
     * @var array(defferedTokenName => value)
     */
    static private $currentDeferredTokens;
    /**
     * The current list of tokens (to aid in debugging errors)
     * @var array(string)
     */
    static public $currentTokens;
    /**
     * The index of the currently executing token
     * @var int
     */
    static public $currentTokenIndex;
    static public $currentTemplateString;

    /**
     * The search path used in getPageTemplate
     * @var array(string)
     */
    static private $pageSearchPath = array(
        '',         // Allow complete path spec
        TEMPLATES   // Add templates directory
    );

    static function addPageSearchPath($path) {
        array_unshift(self::$pageSearchPath, $path);
    }

    /**
     * Gets the specified page template
     * @param string $name The name of the page template file
     * @return Template
     */
    static function getPageTemplate($name) {
        $t = new Template();

        $t->setTemplate($name);

        return $t;
    }

    public function __construct($templateString = null)
    {
        if ($templateString) {
            $this->templateString = $templateString;
        } else {
            $this->setTemplate('default');
        }

        // Run Construct hook events
        foreach (self::$constructorHooks as $hook) {
            if (is_array($hook)) {
                call_user_func($hook, $this);
            } else {
                $hook($this);
            }
        }
    }

    /**
     * Sets the template string
     * @param Template|string $spec Template to copy from or path to template file
     */
    public function setTemplate($spec) {
        if ($spec instanceOf Template) {
            // Copy template string from another template
            $this->templateString = $spec->templateString;
        } else {
            $ts = null;
            // Search for the template
            foreach (self::$pageSearchPath as $path) {
                if (file_exists($path.$spec.'.html')) {
                    $ts = $path.$spec.'.html';
                    break;
                } else if (file_exists($path.$spec)) {
                    $ts = $path.$spec;
                    break;
                }
            }
            if (!$ts) {
                throw new TemplateNotFoundException($spec);
            }
            $this->templateString = file_get_contents($ts, FILE_TEXT);
        }
    }

    public function __set($name, $value) {
        $this->set($name, $value);
    }

    public function set($name, $value) {
        $this->templateVariables[$name] = $value;
    }
    
    public function addContent($content) {
        if (!isset($this->templateVariables['content'])) $this->templateVariables['content'] = '';
        $this->templateVariables['content'] .= $content;
    }

    public function process()
    {
        if (self::$currentProcessor) {
            throw new DeadlockException('Multiple pages running process()');
        }

        self::$currentProcessor = $this;
        self::$currentTokenIndex = 0;
        self::$currentTemplateString = $this->templateString;
        self::$currentDeferredTokens = array('template' =>array(), 'variable' => array());

        // Run a deferred match on all \{ and \} instances. These will be replaced before content is output, but not included
        // in token parsing.
        $content = preg_replace('/\\\\({|})/e', 'Template::$currentProcessor->createDeferredToken("\\1", "template")', $this->templateString);

        // Save tokens to an array for easier debugging
        preg_match_all('/({[^}]+})/', $content, $matches);
        self::$currentTokens = array_shift($matches);

        // Runs a regex replace on the template string, calling Template::$currentProcessor->processToken on each template token
        $content = preg_replace('/{([^}]+)}/e', 'Template::$currentProcessor->processToken("\\1")', $content);

        // Replace deferred tokens
        $content = str_replace(array_keys(self::$currentDeferredTokens["template"]),
                               array_values(self::$currentDeferredTokens["template"]), $content);

        self::$currentProcessor = null;

        return $content;
    }

    /**
     * Creates a deferred token
     * @param <type> $tok
     */
    public function createDeferredToken($tok, $section)
    {
        static $deferredTokenCount = 0;
        $key = '___deferred__token__'.$deferredTokenCount++;
        self::$currentDeferredTokens[$section][$key] = $tok;

        return $key;
    }

    /**
     * Wrapper function which calls processKeyword, and keeps track of the
     * current token index
     * @param string $token
     * @return string
     */
    public function processToken($token)
    {
        $result = $this->processKeyword($token);
        self::$currentTokenIndex++;
        return $result;
    }

    /**
     * Process a variable string and return it's value.
     * Will support features such as 'variable#index' and 'variable#count' (arrays)
     *
     * Supported features:
     *  true,false
     *  "Quoted" and 'Quoted' strings
     *  (number, anything covered by is_numeric)
     *  variable#value
     *  variable#count
     *
     * Modifiers:
     *  variable#lower
     *  variable#upper
     *  variable#length
     *  variable#int
     *  variable#float
     *
     * @param string $variable The variable to search for
     * @param int $flags Any mixture of TF_ flags
     * @return string The variable value
     */
    private function processVariable($variable, $flags = self::TF_NONE)
    {
        $seperator = '#';
        $parts = explode('#', $variable);
        if (!isset($parts[1])) $parts[1] = 'value';

        $variable = $parts[0];

        // Create deferred tokens for escaped characters
        // Escapes: \', \"
        self::$currentDeferredTokens["variable"] = array();
        $variable = preg_replace('/\\\\(\'|")/e', 'Template::$currentProcessor->createDeferredToken("\\1", "variable")', $variable);

        if (isset($this->templateVariables[$parts[0]]) && !is_numeric($variable)) {
            $var = $this->templateVariables[$parts[0]];
        } else {
            if (!($flags & self::TF_NOTPLAINTEXT)) {
                $start = substr($variable, 0, 1);
                $end   = substr($variable, strlen($variable) - 1, 1);
                if ('"' == $start || "'" == $start) {
                    if ($start == $end) {
                        // 'Quoted' or "Quoted" string
                        $var = substr($variable, 1, strlen($variable) - 2);
                    } else {
                        // Mis-typed or unterminated quote, shoud we bail, accept or process as a quoted string?
                        // Eg: 'Quoted
                        $var = $var;
                    }
                } else if (is_numeric($variable)) {
                    // Numeric
                    $var = (double)$variable;
                } else {
                    // Constants - returned immediately, cannot apply modifiers
                    switch(strtolower($variable)) {
                        case 'true':
                        case 't':
                            return true;

                        case 'false':
                        case 'f':
                            return false;

                        default:
                            // Not known
                            if (self::$strictMode && !($flags & self::TF_VARIABLENOTREQUIRED)) {
                                throw new TemplateVariableExpansionException($variable, 'Variable not found');
                            }
                            return null;
                    }
                }
            } else {
                if (self::$strictMode && !($flags & self::TF_VARIABLENOTREQUIRED)) {
                    throw new TemplateVariableExpansionException($variable, 'Variable not found');
                }
                return null;
            }
        }

        switch(strtolower($parts[1])) {
            case '':
            case 'value':
                // No transformation
                break;

            case 'count':
                if (!is_array($var)) throw new TemplateExpansionException($parts, 'Not an array');
                $var = count($var);
                break;

            case 'upper':
                $var = strtoupper($var);
                break;

            case 'lower':
                $var = strtolower($var);
                break;

            case 'length':
                $var = strlen($var);
                break;

            case 'int':
                $var = (int)$var;
                break;

            case 'float':
                $var = (float)$var;
                break;

            default:
                throw new TemplateExpansionException($parts, 'Unknown expansion modifier');
        }

        // Replace deferred tokens
        return str_replace(array_keys(self::$currentDeferredTokens["variable"]),
                           array_values(self::$currentDeferredTokens["variable"]), $var);
    }

    /**
     * Performs template keyword expansion
     * Available keywords:
     *
     *  -- Standard --
     *    [write]:variable      Write variable value
     *    if:variable[:operator][:comparison]   Open if block
     *    /if                   Close if block
     *    strict[:on|:off]      Enable or disable strict mode
     *  -- Debugging --
     *    stats         Display stats
     *    dump          Dump all variables
     *    export        var_export on the given variable's value
     *
     * Notes on "if" processing:
     *   operator may be one of:
     *      (blank)     alias of "==:true"
     *      isset       Check is variable is set
     *      =,==,>,<    Comparison operators, requires comparison variable
     *   comparison follows the rules of processVariable
     *
     * @param string $keyword
     * @return string The resulting string
     */
    public function processKeyword($keyword)
    {
        // Break $keyword down into component parts and process
        $parts = explode(':', $keyword);

        // Whether we should execute the current keyword (if not ending control)
        $exec = end($this->templateStack);

        switch(strtolower($parts[0])) {
            /**
             * Writes a variable
             * {[write]:variable}
             */
            case '':
            case 'write':
                if (!$exec) return '';
                return $this->processVariable($parts[1]);

            case 'strict':
                switch(strtolower(isset($parts[1]) ? $parts[1] : 'on')) {
                    case 'on':
                        self::$strictMode = true;
                        return null;

                    case 'off':
                        self::$strictMode = false;
                        return null;
                }
                throw new TemplateException('Strict method not known');

            case 'stats':
                if (!$exec) return '';
                global $execStartTime, $autoLoaderTime;
                $totalTime = microtime(true) - $execStartTime;
                Stats::set('ExecTime', $totalTime - $autoLoaderTime);
                Stats::set('TotalTime', $totalTime);
                return '<pre>'.var_export(Stats::getStats(), true).'</pre>';

            case 'dump':
                if (!$exec) return '';
                return '<pre>'.var_export($this->templateVariables, true).'</pre>';

            case 'export':
                if (!$exec) return '';
                return '<pre>'.var_export($this->processVariable($parts[1]), true);

            /**
             * A comment. Not written to template.
             * {command:Foo bar etc}
             */
            case 'comment':
                return '';

            /**
             * Control flow: Begins if block
             * {if:variable[:isset]}
             */
            case 'if':
                // Skip execution if $exec is false
                // This stops ifs from processing if their parent result was false
                if ($exec == false) {
                    $result = false;
                } else {
                    $operator = isset($parts[2]) ? $parts[2] : '';

                    // Variable is not required (eg, an 'if var exists'), but it must actually be a variable (not a string)
                    $var = $this->processVariable($parts[1], self::TF_NONE | self::TF_VARIABLENOTREQUIRED);
                    // Can be plain text, or not even exist
                    $comparisonTo = isset($parts[3]) ? $this->processVariable($parts[3]) : true;
                    
                    switch($operator) {
                        case '':
                            // Check for boolean
                            $result = ($var ? true : false);
                            break;

                        case 'isset':
                            $result = ($var !== null);
                            break;

                        case '=':
                        case '==':
                            $result = ($var == $comparisonTo);
                            break;

                        case '>':
                            $result = ($var > $comparisonTo);
                            break;

                        case '<':
                            $result = ($var < $comparisonTo);
                            break;

                        case '>=':
                        case '=>':
                            $result = ($var >= $comparisonTo);
                            break;

                        case '<=':
                        case '=<':
                            $result = ($var <= $comparisonTo);
                            break;

                        default:
                            throw new TemplateComparisonOperatorNotSupported($operator);
                    }
                }
                $this->templateStack[] = $result;
                return '';

            case 'else':
                // Alternates the current stack's value
                // Checks parent stack to ensure we should alternate (ie, if parent is false, we stay false)
                $this->templateStack[key($this->templateStack)] = !$exec && prev($this->templateStack);
                break;

            case '/if':
                if (count($this->templateStack) == 1) {
                    throw new TemplateStackUnderflowException('/if');
                }
                array_pop($this->templateStack);
                return '';

            default:
                throw new UnknownTemplateCommandException($parts[0]);
        }
    }
}
?>
