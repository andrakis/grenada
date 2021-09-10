<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Page to display
 *
 * @author Daedalus
 */
abstract class Page implements Processable {
    /**
     * Template being used by the page
     * @var Template
     */
    private $template;

    /**
     *
     * @var string
     */
    protected $parameters;

    static public function executePage(Page $classInstance) {
        session_start();
        try {
            self::flush($classInstance->process());
        } catch (Exception $ex) {
            // Reset template processor and show error page
            Template::$currentProcessor = null;
            $t = new Template();
            $t->setTemplate(TEMPLATES.'error.html');
            $t->errorTitle = 'Unhandled Exception: '.get_class($ex);
            $t->errorDescription = $ex->getMessage();
            $t->errorStackTrace  = $ex->getTraceAsString();
            if ($ex instanceOf TemplateException) {
                $t->templateDetails = $ex->getTemplateExceptionDetails();
            }
            self::flush($t->process());
        }
        exit;
    }

    static private function flush($content = null)
    {
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
        if ($content !== null) echo $content;
        ob_end_flush();
    }

    /**
     * Creates a Page
     * @param string $parameters Any parameters passed to the page
     * @param Template $template Template to use for page
     */
    public function __construct($parameters = null, $template = null)
    {
        $this->parameters = $parameters;
        $this->template = $template ? $template : new Template();
    }

    /**
     *
     */
    final public function process()
    {
        $this->execute($this->template);
        return $this->template->process();
    }

    /**
     *
     */
    abstract protected function execute(Template $template);

    public function setTemplate($path)
    {
        $this->templateString = file_get_contents($path, FILE_TEXT);
    }

    public function headerRedirect($url)
    {
        $this->flush();

    }
}
?>
