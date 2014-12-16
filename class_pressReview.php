<?php
/**
 * @file class_pressReview.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.PressReview.pressReview
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti
 * @author abidibo
 */

/**
 * @namespace Gino.App.PressReview
 * @description Namespace dell'applicazione PressReview
 */
namespace Gino\App\PressReview;

use \Gino\Loader;
use \Gino\View;
use \Gino\Session;

require_once('class.PressReviewItem.php');
require_once('class.PressNewspaper.php');

/**
 * @brief Classe di tipo Gino.Controller per la gestione di una rassegna stampa
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti
 * @author abidibo
 * @version 0.1.0
 */
class pressReview extends \Gino\Controller
{

    private $_options,
            $_newspaper_logo_width,
            $_last_tpl_number,
            $_list_tpl_ifp;
    public $_optionsLabels;


    /**
     * @brief Costruttore
     *
     * @param $instance_id id istanza
     * @return istanza di Gino.App.PressReview.pressReview
     */
    public function __construct($instance_id)
    {
        parent::__construct($instance_id);

        $this->_tbl_opt = 'press_review_opt';

        /* options */
        $options_dft = array(
            'newspaper_logo_width'=>80,
            'last_tpl_number'=>3,
            'list_tpl_ifp'=>10
        );
        $this->_newspaper_logo_width = $this->setOption('newspaper_logo_width', array('value'=>$options_dft['newspaper_logo_width']));
        $this->_last_tpl_number = $this->setOption('last_tpl_number', array('value'=>$options_dft['last_tpl_number']));
        $this->_list_tpl_ifp = $this->setOption('list_tpl_ifp', array('value'=>$options_dft['list_tpl_ifp']));

        $this->_options = loader::load('Options', array($this));
        $this->_optionsLabels = array(
            "newspaper_logo_width"=>array(
                'label'=>_("Larghezza logo (px)"), 
                'value'=>$options_dft['newspaper_logo_width']
            ),
            "last_tpl_number"=>array(
                'label'=>_("Numero ultimi articoli"),
                'value'=>$options_dft['last_tpl_number']
            ),
            "list_tpl_ifp"=>array(
                'label'=>_("Numero di articoli per pagina"),
                'value'=>$options_dft['list_tpl_ifp']
            )
        );
    }

    /**
     * @brief Restituisce alcune proprietà della classe utili per la generazione di nuove istanze
     * @return lista delle proprietà utilizzate per la creazione di istanze di tipo events (tabelle, css, viste, folders)
     */
    public static function getClassElements() 
    {
        return array(
            "tables"=>array(
                'press_review_item',
                'press_review_newspaper',
                'press_review_opt'
            ),
            "css"=>array(
                'pressReview.css',
            ),
            "views" => array(
                'last.php' => _('Ultimi articoli'),
                'archive.php' => _('Archivio completo'),
                'feed_rss.php' => _('Feed RSS')
            ),
            "folderStructure"=>array (
                CONTENT_DIR.OS.'pressReview'=> array(
                    'logo' => null,
                    'papers' => null
                )
            )
        );
    }

    /**
     * @brief Metodo invocato quando viene eliminata un'istanza di tipo rassegna stampa
     *
     * Si esegue la cancellazione dei dati da db e l'eliminazione di file e directory
     * @return TRUE
     */
    public function deleteInstance() 
    {
        $this->requirePerm('can_admin');

        /** eliminazione articoli */
        PressReviewItem::deleteInstance($this);

        /** eliminazione newspapers */
        PressNewspaper::deleteInstance($this);

        /** eliminazione opzioni */
        $opt_id = $this->_db->getFieldFromId($this->_tbl_opt, "id", "instance", $this->_instance);
        \Gino\Translation::deleteTranslations($this->_tbl_opt, $opt_id);
        $result = $this->_db->delete($this->_tbl_opt, "instance=".$this->_instance);

        /** delete css files */
        $classElements = $this->getClassElements();
        foreach($classElements['css'] as $css) {
            unlink(APP_DIR.OS.$this->_class_name.OS.\Gino\baseFileName($css)."_".$this->_instance_name.".css");
        }

        /** eliminazione views */
        foreach($classElements['views'] as $k => $v) {
            unlink($this->_view_dir.OS.\Gino\baseFileName($k)."_".$this->_instance_name.".php");
        }

        /** delete folder structure */
        foreach($classElements['folderStructure'] as $fld=>$fldStructure) {
            \Gino\deleteFileDir($fld.OS.$this->_instance_name, TRUE);
        }

        return TRUE;
    }

    /**
     * @brief Metodi pubblici disponibili per inserimento in layout (non presenti nel file events.ini) e menu (presenti nel file events.ini)
     * @return lista metodi NOME_METODO => array('label' => LABEL, 'permissions' = PERMISSIONS)
     */
    public static function outputFunctions() 
    {
        $list = array(
            "last" => array("label"=>_("Lista utimi articoli rassegna stampa"), "permissions"=>array()),
            "archive" => array("label"=>_("Archivio rassegna stampa"), "permissions"=>array()),
            "feedRSS" => array("label"=>_("Feed RSS"), "permissions"=>array()),
        );

        return $list;
    }

    /**
     * @brief Percorso assoluto alla cartella dei contenuti
     *
     * @param string $type tipologia contenuto (logo, papers)
     * @return percorso assoluto
     */
    public function getTypeBaseAbsPath($type) 
    {
        return $this->_data_dir.OS.$type;
    }

    /**
     * @brief Percorso relativo alla cartella dei contenuti
     *
     * @param string $type tipologia contenuto (logo, papers)
     * @return percorso relativo
     */
    public function getTypeBasePath($type)
    {
        return $this->_data_www.'/'.$type;
    }

    /**
     * Getter larghezza logo testate
     *
     * @return largheza di ridimensionamento
     */
    public function getNewspaperLogoWidth() {

        return $this->_newspaper_logo_width;

    }

    /**
     * @brief Esegue il download clientside del documento indicato da url ($doc_id)
     * @param \Gino\Http\Request $request
     * @throws Gino.Exception.Exception404 se il documento non viene trovato
     * @return Gino.Http.ResponseFile
     */
    public function download(\Gino\Http\Request $request) {

        $doc_id = \Gino\cleanVar($request->GET, 'id', 'int');

        if(!empty($doc_id)) {
            $pr = new pressReviewItem($doc_id, $this);
            if(!$pr->id) {
                throw new \Gino\Exception\Exception404();
            }

            $file = $pr->file;
            if($file) {
                $full_path = $this->getTypeBaseAbsPath('papers').OS.$file;
                return \Gino\download($full_path);
            }
            else {
                throw new \Gino\Exception\Exception404();
            }
        }

        throw new \Gino\Exception\Exception404();
    }

    /**
     * @brief Front end ultime rassegna stampa
     *
     * @return html, lista ultime rassegna stampa
     */
    public function last() {

        $this->_registry->addCss($this->_class_www."/pressReview_".$this->_instance_name.".css");

        $items = PressReviewItem::objects($this, array('order'=>'date DESC', 'limit'=>array(0, $this->_last_tpl_number)));

        $view = new View($this->_view_dir);

        $view->setViewTpl('last_'.$this->_instance_name);
        $view->assign('section_id', 'last_pressReview_'.$this->_instance_name);
        $view->assign('feed_url', $this->link($this->_instance_name, 'feedRSS'));
        $view->assign('archive_url', $this->link($this->_instance_name, 'archive'));
        $view->assign('items', $items);

        return $view->render();

    }

    /**
     * @brief Front end archivio rassegna stampa con ricerca
     *
     * @param \Gino\Http\Request $request
     * @return Gino.Http.Response
     */
    public function archive(\Gino\Http\Request $request) {

        $this->_registry->addCss($this->_class_www."/pressReview_".$this->_instance_name.".css");
        $session = $request->session;

        $this->sessionSearch();

        $where_arr = array();
        if($session->{'pressReviewSearch'.$this->_instance}['newspaper']) {
            $where_arr[] = "newspaper='".$session->{'pressReviewSearch'.$this->_instance}['newspaper']."'";
        }
        if($session->{'pressReviewSearch'.$this->_instance}['text']) {
            $where_arr[] = "(title LIKE '%".$session->{'pressReviewSearch'.$this->_instance}['text']."%' OR notes LIKE '%".$session->{'pressReviewSearch'.$this->_instance}['text']."%')";
        }
        if($session->{'pressReviewSearch'.$this->_instance}['date_from']) {
            $where_arr[] = "date >= '".\Gino\dateToDbDate($session->{'pressReviewSearch'.$this->_instance}['date_from'], '/')."'";
        }
        if($session->{'pressReviewSearch'.$this->_instance}['date_to']) {
            $where_arr[] = "date <= '".\Gino\dateToDbDate($session->{'pressReviewSearch'.$this->_instance}['date_to'], '/')."'";
        }

        $where = count($where_arr) ? implode(' AND ', $where_arr) : '';

        $prs_number = PressReviewItem::getCount($this, array('where'=>$where));
        $paginator = Loader::load('Paginator', array($prs_number, $this->_list_tpl_ifp));
        $limit = $paginator->limitQuery();

        $items = PressReviewItem::objects($this, array('where'=>$where, 'order'=>'date DESC', 'limit'=>$limit));

        $view = new View($this->_view_dir, 'archive_' . $this->_instance_name);

        $view->assign('section_id', 'archive_pressReview_'.$this->_instance_name);
        $view->assign('feed_url', $this->link($this->_instance_name, 'feedRSS'));
        $view->assign('items', $items);
        $view->assign('open_form', count($where_arr) ? TRUE : FALSE);
        $view->assign('search_form', $this->formSearch());
        $view->assign('pagination', $paginator->pagination());

        $document = new \Gino\Document($view->render());
        return $document();

    }

    /**
     * @brief Form di ricerca in archivio
     *
     * @return html, form ricerca archivio
     */
    private function formSearch() {

        $session = Session::instance();

        $myform = Loader::load('Form', array('form_search_pr', 'post', false));
        $form_search = $myform->open($this->link($this->_instance_name, 'archive'), FALSE, '');
        $form_search .= $myform->cselect('search_newspaper', $session->{'pressReviewSearch'.$this->_instance}['newspaper'], PressNewspaper::getForSelect($this), _('Testata'), array());
        $form_search .= $myform->cinput_date('search_from', $session->{'pressReviewSearch'.$this->_instance}['date_from'], _('Da'), array());
        $form_search .= $myform->cinput_date('search_to', $session->{'pressReviewSearch'.$this->_instance}['date_to'], _('A'), array());
        $form_search .= $myform->cinput('search_text', 'text', \Gino\htmlInput($session->{'pressReviewSearch'.$this->_instance}['text']), _('Titolo/Note'), array('size'=>20, 'maxlength'=>40));
        $submit_all = $myform->input('submit_search_all', 'submit', _('tutti'), array('classField'=>'submit'));
        $form_search .= $myform->cinput('submit_search', 'submit', _('cerca'), '', array('classField'=>'submit', 'text_add'=>' '.$submit_all));
        $form_search .= $myform->close();

        return $form_search;
    }

    /**
     * @brief Imposta la ricerca in sessione
     *
     * @return void
     */
    private function sessionSearch() {

        $request = \Gino\Http\Request::instance();
        $session = $request->session;

        if(isset($request->POST['submit_search_all'])) {
            $search = null;
            $session->{'pressReviewSearch'.$this->_instance} = $search;
        }

        if(!$session->{'pressReviewSearch'.$this->_instance}) {
            $search = array(
                'newspaper' => null,
                'text' => null,
                'date_from' => null,
                'date_to' => null
            );
        }

        if(isset($request->POST['submit_search'])) {
            if(isset($request->POST['search_newspaper'])) {
                $search['newspaper'] = \Gino\cleanVar($request->POST, 'search_newspaper', 'int', '');
            }
            if(isset($request->POST['search_text'])) {
                $search['text'] = \Gino\cleanVar($request->POST, 'search_text', 'string', '');
            }
            if(isset($request->POST['search_from'])) {
                $search['date_from'] = \Gino\cleanVar($request->POST, 'search_from', 'string', '');
            }
            if(isset($request->POST['search_to'])) {
                $search['date_to'] = \Gino\cleanVar($request->POST, 'search_to', 'string', '');
            }
            $session->{'pressReviewSearch'.$this->_instance} = $search;
        }

    }

    /**
     * @brief Interfaccia di amministrazione del modulo
     * @param \Gino\Http\Request $request
     * @return Gino.Http.Response, interfaccia di backoffice
     */
    public function manageDoc(\Gino\Http\Request $request)
    {
        $this->requirePerm('can_admin');

        $block = \Gino\cleanVar($request->GET, 'block', 'string');

        $link_frontend = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=frontend'), _('Frontend'));
        $link_options = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=options'), _('Opzioni'));
        $link_newspaper = sprintf('<a href="%s">%s</a>', $this->linkAdmin(array(), 'block=newspaper'), _('Testate'));
        $link_dft = sprintf('<a href="%s">%s</a>', $this->linkAdmin(), _('Articoli'));
        $sel_link = $link_dft;

        if($block == 'frontend' && $this->userHasPerm('can_admin')) {
            $backend = $this->manageFrontend();
            $sel_link = $link_frontend;
        }
        elseif($block=='options') {
            $backend = $this->manageOptions();
            $sel_link = $link_options;
        }
        elseif($block == 'newspaper') {
            $backend = $this->manageNewspaper();
            $sel_link = $link_newspaper;
        }
        else {
            $backend = $this->manageItem();
        }

        if(is_a($backend, '\Gino\Http\Response')) {
            return $backend;
        }

        // groups privileges
        /* $links_array = array($link_frontend, $link_options, $link_dft); */
        $links_array = array($link_frontend, $link_options, $link_newspaper, $link_dft);

        $view = new View(null, 'tab');
        $dict = array(
          'title' => _('Rassegna stampa'),
          'links' => $links_array,
          'selected_link' => $sel_link,
          'content' => $backend
        );

        $document = new \Gino\Document($view->render($dict));
        return $document();
    }

    /**
     * @brief Interfaccia di amministrazione articoli rassegna stampa
     *
     * @return Gino.Http.Redirect oppure html, interfaccia di amministrazione
     */
    public function manageItem()
    {
        $admin_table = Loader::load('AdminTable', array($this, array()));

        $backend = $admin_table->backoffice(
            'PressReviewItem',
            array(
                'filter_fields' => array('newspaper', 'title', 'date', 'notes'),
                'list_display' => array('id', 'newspaper', 'title', 'date', 'notes')
            ), // display options
            array(), // form options
            array()  // fields options
        );

        return $backend;
    }

    /**
     * @brief Interfaccia di amministrazione newspaper
     *
     * @return Gino.Http.Redirect oppure html, interfaccia di amministrazione
     */
    public function manageNewspaper()
    {
        $admin_table = Loader::load('AdminTable', array($this, array()));

        $backend = $admin_table->backoffice(
            'PressNewspaper',
            array(
                'filter_fields' => array('name')
            ), // display options
            array(), // form options
            array()  // fields options
        );

        return $backend;
    }

    /**
     * @brief Metodo per la definizione di parametri da utilizzare per il modulo "Ricerca nel sito"
     *
     * Il modulo "Ricerca nel sito" di Gino base chiama questo metodo per ottenere informazioni riguardo alla tabella, campi, pesi etc...
     * per effettuare la ricerca dei contenuti.
     *
     * @return array[string]mixed array associativo contenente i parametri per la ricerca
     */
    public function searchSite() {

        return array(
            "table"=>PressReviewItem::$table,
            "selected_fields"=>array("id", "date", array("highlight"=>true, "field"=>"title"), array("highlight"=>true, "field"=>"notes")),
            "required_clauses"=>array("instance"=>$this->_instance),
            "weight_clauses"=>array("title"=>array("weight"=>3), "notes"=>array("weight"=>1))
        );
    }

    /**
     * @brief Definisce la presentazione del singolo item trovato a seguito di ricerca (modulo "Ricerca nel sito")
     *
     * @param array $results array associativo contenente i risultati della ricerca
     * @return html, presentazione elemento
     */
    public function searchSiteResult($results) {

        $obj = new PressReviewItem($results['id'], $this);

        $link = $obj->resourceUrl();

        $buffer = "<div>".\Gino\dbDatetimeToDate($results['date'], "/")." <a href=\"".$link."\">";
        $buffer .= $results['title'] ? \Gino\htmlChars($results['title']) : \Gino\htmlChars($obj->ml('title'));
        $buffer .= "</a></div>";

        if($results['notes']) $buffer .= "<div class=\"search_text_result\">...".\Gino\htmlChars($results['notes'])."...</div>";

        return $buffer;

    }

    /**
     * @brief Genera un feed RSS standard che presenta gli ultimi 50 articoli della rassegna stampa
     *
     * @param \Gino\Http\Request $request
     * @return Gino.Http.Response
     */
    public function feedRSS(\Gino\Http\Request $request) {

        $title_site = $this->_registry->sysconf->head_title;
        $module = new \Gino\App\Module\ModuleInstance($this->_instance);
        $title = $module->label.' | '.$title_site;
        $description = $module->description;

        $prs = PressReviewItem::objects($this, array('order'=>'date DESC', 'limit'=>array(0, 50)));

        $view = new \Gino\View($this->_view_dir, 'feed_rss_'.$this->_instance_name);
        $dict = array(
            'title' => $title,
            'description' => $description,
            'request' => $this->_registry->request,
            'prs' => $prs
        );

        $response = new \Gino\Http\Response($view->render($dict));
        $response->setContentType('text/xml');
        return $response;

    }

}
