<?php
/**
 * \file class_pressReview.php
 * Contiene la definizione ed implementazione della classe pressReview.
 * 
 * @version 0.1
 * @copyright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

/**
 * Caratteristiche, opzioni configurabili da backoffice ed output disponibili per i template e le voci di menu.
 *
 * CARATTERISTICHE    
 *  
 * Modulo di gestione della rassegna stampa 
 *
 * OPZIONI CONFIGURABILI
 * - titolo ultimi articoli rassegna stampa
 * - titolo elenco articoli rassegna stampa
 * - larghezza logo testate
 * - codice singolo elemento nella vista ultimi articoli rassegna stampa
 * - numero di ultimi articoli
 * - codice singolo elemento nella vista archivio articoli rassegna stampa
 * - numero di articoli per pagina
 *
 * OUTPUTS
 * - ultimi articoli rassegna stampa
 * - archivio articoli rassegna stampa
 * - feed RSS
 */
require_once('class.pressReviewItem.php');
require_once('class.pressNewspaper.php');

/**
 * @defgroup gino-pressReview
 * Modulo di gestione rassegna stampa
 *
 * Il modulo contiene anche dei css, javascript e file di configurazione.
 *
 */

/**
 * \ingroup gino-pressReview
 * Classe per la gestione di una rassegna stampa.
 *
 * Gli output disponibili sono:
 *
 * - ultimi articoli rassegna stampa
 * - archivio articoli rassegna stampa
 * - feed RSS
 * 
 * @version 0.1
 * @copyright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class pressReview extends AbstractEvtClass {

	/**
	 * @brief titolo della view ultimi articoli rassegna stampa  
	 */
	private $_title_last;

	/**
	 * @brief titolo della view archivio articoli rassegna stampa  
	 */
	private $_title_list;

	/**
	 * @brief Template elemento ultimi articoli rassegna stampa  
	 */
	private $_last_tpl_code;

	/**
	 * @brief numero di ultimi articoli  
	 */
	private $_last_tpl_number;

	/**
	 * @brief Template elemento archivio articoli rassegna stampa  
	 */
	private $_list_tpl_code;

	/**
	 * @brief numero di articoli per pagina 
	 */
	private $_list_tpl_ifp;

	/**
	 * @brief larghezza logo testata  
	 */
	private $_newspaper_logo_width;

	/**
	 * @brief Tabella di opzioni 
	 */
	private $_tbl_opt;

	/**
	 * @brief Tabella di associazione utenti/gruppi 
	 */
	private $_tbl_usr;

	/**
	 * Percorso assoluto alla directory contenente le viste 
	 */
	private $_view_dir;

	/*
	 * Parametro action letto da url 
	 */
	private $_action;

	/*
	 * Parametro block letto da url 
	 */
	private $_block;

	/**
	 * Costruisce un'istanza di tipo pressReview
	 *
	 * @param int $mdlId id dell'istanza di tipo pressReview
	 * @return istanza di pressReview
	 */
	function __construct($mdlId) {

		parent::__construct();

		$this->_instance = $mdlId;
		$this->_instanceName = $this->_db->getFieldFromId($this->_tbl_module, 'name', 'id', $this->_instance);
		$this->_instanceLabel = $this->_db->getFieldFromId($this->_tbl_module, 'label', 'id', $this->_instance);

		$this->_data_dir = $this->_data_dir.$this->_os.$this->_instanceName;
		$this->_data_www = $this->_data_www."/".$this->_instanceName;

		$this->_tbl_opt = 'press_review_opt';
		$this->_tbl_usr = 'press_review_usr';

		$this->setAccess();
		$this->setGroups();

		$this->_view_dir = dirname(__FILE__).OS.'view';

		$last_tpl_code = '<article><p>{{ newspaper_logo|class:left}}</p><h1>{{ title|link }}</h1><p>{{ notes|chars:80 }}</p><div class="null"></div></article>';
		$list_tpl_code = '<dt><p>{{ newspaper_logo|class:left }}</p><h2>{{ date }} - {{ title|link }}</h2></dt><dd></p>{{ notes }}<div class="null"></div></dd>';

		$this->_optionsValue = array(
			'title_last'=>_("Ultimi articoli"),
			'title_list'=>_("Rassegna stampa"),
			'newspaper_logo_width'=>80,
			'last_tpl_code'=>$last_tpl_code,
			'last_tpl_number'=>3,
			'list_tpl_code'=>$list_tpl_code,
			'list_tpl_ifp'=>10
		);

		$code_exp = _("Le proprietà della rassegna stampa devono essere inserite all'interno di doppie parentesi {{ proprietà }}. Proprietà disponibili:<br/>");
		$code_exp .= "<ul>";
		$code_exp .= "<li><b>newspaper</b>: "._('testata con link (se presente)')."</li>";
		$code_exp .= "<li><b>newspaper_logo</b>: "._('logo testata')."</li>";
		$code_exp .= "<li><b>title</b>: "._('titolo')."</li>";
		$code_exp .= "<li><b>notes</b>: "._('Note')."</li>";
		$code_exp .= "<li><b>date</b>: "._('data')."</li>";
		$code_exp .= "<li><b>social</b>: "._('condivisione social networks')."</li>";
		$code_exp .= "</ul>";
		$code_exp .= _("Inoltre si possono eseguire dei filtri o aggiungere link facendo seguire il nome della proprietà dai caratteri '|filtro'. Disponibili:<br />");
		$code_exp .= "<ul>";
		$code_exp .= "<li><b><span style='text-style: normal'>|link</span></b>: "._('aggiunge il link che porta alla risorsa (download del file o url esterno)')."</li>";
		$code_exp .= "<li><b><span style='text-style: normal'>newspaper_logo|class:name_class</span></b>: "._('aggiunge la classe name_class all\'immagine del logo')."</li>";
		$code_exp .= "</ul>";

		$this->_title_last = htmlChars($this->setOption('title_last', array('value'=>$this->_optionsValue['title_last'], 'translation'=>true)));
		$this->_title_list = htmlChars($this->setOption('title_list', array('value'=>$this->_optionsValue['title_list'], 'translation'=>true)));
		$this->_newspaper_logo_width = $this->setOption('newspaper_logo_width', array('value'=>$this->_optionsValue['newspaper_logo_width']));
		$this->_last_tpl_code = $this->setOption('last_tpl_code', array('value'=>$this->_optionsValue['last_tpl_code'], 'translation'=>true));
		$this->_last_tpl_number = $this->setOption('last_tpl_number', array('value'=>$this->_optionsValue['last_tpl_number']));
		$this->_list_tpl_code = $this->setOption('list_tpl_code', array('value'=>$this->_optionsValue['list_tpl_code'], 'translation'=>true));
		$this->_list_tpl_ifp = $this->setOption('list_tpl_ifp', array('value'=>$this->_optionsValue['list_tpl_ifp']));

		$this->_options = new options($this->_className, $this->_instance);

		$this->_optionsLabels = array(
			"title_last"=>array(
				'label'=>_("Titolo ultimi articoli rassegna stampa"), 
				'value'=>$this->_optionsValue['title_last'], 
				'section'=>true, 
				'section_title'=>_('Titoli delle viste pubbliche')
			),
			"title_list"=>array(
				'label'=>_("Titolo archivio articoli rassegna stampa"),
				'value'=>$this->_optionsValue['title_list']
			),
			"newspaper_logo_width"=>array(
				'label'=>_("Larghezza logo (px)"), 
				'value'=>$this->_optionsValue['newspaper_logo_width'], 
				'section'=>true, 
				'section_title'=>_('Testata')
			),
			"last_tpl_code"=>array(
				'label'=>array(_("Template singolo elemento vista ultimi articoli rassegna stampa"), $code_exp), 
				'value'=>$this->_optionsValue['last_tpl_code'],
				'section'=>true, 
				'section_title'=>_('Opzioni vista ultimi articoli rassegna stampa'),
				'section_description'=>"<p>"._('Il template verrà utilizzato per ogni rassegna stampa ed inserito all\'interno di una section')."</p>"
			), 
			"last_tpl_number"=>array(
				'label'=>_("Numero ultimi articoli"),
				'value'=>$this->_optionsValue['last_tpl_number']
			),
			"list_tpl_code"=>array(
				'label'=>array(_("Template singolo elemento vista archivio rassegna stampa"), $code_exp), 
				'value'=>$this->_optionsValue['list_tpl_code'],
				'section'=>true, 
				'section_title'=>_('Opzioni vista archivio rassegna stampa'),
				'section_description'=>"<p>"._('Il template verrà utilizzato per ogni rassegna stampa ed inserito all\'interno di una lista <b>dd</b>, fornire quindi i <b>dt</b> ed eventualmente <b>dd</b>')."</p>"
			), 
			"list_tpl_ifp"=>array(
				'label'=>_("Numero di articoli per pagina"),
				'value'=>$this->_optionsValue['list_tpl_ifp']
			)
		);

		$this->_action = cleanVar($_REQUEST, 'action', 'string', '');
		$this->_block = cleanVar($_REQUEST, 'block', 'string', '');

	}

	/**
	 * Restituisce alcune proprietà della classe utili per la generazione di nuove istanze
	 *
	 * @static
	 * @return lista delle proprietà utilizzate per la creazione di istanze di tipo pressReview
	 */
	public static function getClassElements() {

		return array(
			"tables"=>array(
				'press_review_item', 
				'press_review_grp', 
				'press_review_newspaper', 
				'press_review_opt', 
				'press_review_usr'
			),
			"css"=>array(
				'press_review.css'
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
	 * Metodo invocato quando viene eliminata un'istanza
	 *
	 * Si esegue la cancellazione dei dati da db e l'eliminazione di file e directory 
	 * 
	 * @access public
	 * @return bool il risultato dell'operazione
	 */
	public function deleteInstance() {

		$this->accessGroup('');

		/*
		 * delete records and translations from table press_review_item
		 */
		$query = "SELECT id FROM ".pressReviewItem::$tbl_item." WHERE instance='$this->_instance'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) 
			foreach($a as $b) 
				language::deleteTranslations(pressReviewItem::$tbl_item, $b['id']);
		
		$query = "DELETE FROM ".pressReviewItem::$tbl_item." WHERE instance='$this->_instance'";	
		$result = $this->_db->actionquery($query);
		
		/*
		 * delete record and translations from table press_review_newspaper
		 */
		$query = "SELECT id FROM ".pressNewspaper::$tbl_newspaper." WHERE instance='$this->_instance'";
		$a = $this->_db->selectquery($query);
		if(sizeof($a)>0) {
			foreach($a as $b) {
				language::deleteTranslations(pressNewspaper::$tbl_newspaper, $b['id']);
			}
		}
		$query = "DELETE FROM ".pressNewspaper::$tbl_newspaper." WHERE instance='$this->_instance'";	
		$result = $this->_db->actionquery($query);

		/*
		 * delete record and translation from table press_review_opt
		 */
		$opt_id = $this->_db->getFieldFromId($this->_tbl_opt, "id", "instance", $this->_instance);
		language::deleteTranslations($this->_tbl_opt, $opt_id);
		
		$query = "DELETE FROM ".$this->_tbl_opt." WHERE instance='$this->_instance'";	
		$result = $this->_db->actionquery($query);
		
		/*
		 * delete group users association
		 */
		$query = "DELETE FROM ".$this->_tbl_usr." WHERE instance='$this->_instance'";	
		$result = $this->_db->actionquery($query);

		/*
		 * delete css files
		 */
		$classElements = $this->getClassElements();
		foreach($classElements['css'] as $css) {
			unlink(APP_DIR.OS.$this->_className.OS.baseFileName($css)."_".$this->_instanceName.".css");
		}

		/*
		 * delete folder structure
		 */
		foreach($classElements['folderStructure'] as $fld=>$fldStructure) {
			$this->deleteFileDir($fld.OS.$this->_instanceName, true);
		}

		return $result;

	}

	/**
	 * Setter per le proprietà group
	 *
	 * Definizione dei gruppi che gestiscono l'accesso alle funzionalità amministrative e non
	 *
	 * @return void
	 */
	private function setGroups(){
		
		// Gestione contenuti
		$this->_group_1 = array($this->_list_group[0], $this->_list_group[1]);
		
	}

	/**
	 * Definizione dei metodi pubblici che forniscono un output per il front-end 
	 * 
	 * Questo metodo viene letto dal motore di generazione dei layout e dal motore di generazione di voci di menu
	 * per presentare una lista di output associati all'istanza di classe. 
	 * 
	 * @static
	 * @access public
	 * @return array[string]array
	 */
	public static function outputFunctions() {

		$list = array(
			"last" => array("label"=>_("Lista utimi articoli rassegna stampa"), "role"=>'1'),
			"archive" => array("label"=>_("Archivio rassegna stampa"), "role"=>'1')
		);

		return $list;
	}

	/**
	 * Percorso assoluto alla cartella dei contenuti 
	 * 
	 * @param string $type tipologia contenuto (logo, papers)
	 * @return percorso assoluto
	 */
	public function getBaseAbsPath($type) {

		return $this->_data_dir.OS.$type;

	}

	/**
	 * Percorso relativo alla cartella dei contenuti 
	 * 
	 * @param string $type tipologia contenuto (logo, papers)
	 * @return percorso relativo
	 */
	public function getBasePath($type) {

		return $this->_data_www.'/'.$type;

	}

	/**
	 * Getter larghezza logo testate 
	 * 
	 * @access public
	 * @return largheza di ridimensionamento
	 */
	public function getNewspaperLogoWidth() {

		return $this->_newspaper_logo_width;

	}

	/**
	 * Esegue il download clientside del documento indicato da url ($doc_id)
	 *
	 * @access public
	 * @return stream
	 */
	public function download() {

		$doc_id = cleanVar($_GET, 'id', 'int', '');

		if(!empty($doc_id)) {
			$pr = new pressReviewItem($doc_id, $this);
			if(!$pr->id) {
				error::raise404();
			}

			$file = $pr->file;
			if($file) {
				$full_path = $this->getBaseAbsPath('papers').$this->_os.$file;
				download($full_path);
			}
			else {
				error::raise404();
			}
		}

		error::raise404();
	}

	/**
	 * Front end ultime news 
	 * 
	 * @access public
	 * @return lista ultime news
	 */
	public function last() {

		$this->setAccess($this->_access_base);

		$registry = registry::instance();

		$objs = pressReviewItem::get($this, array('order'=>'date DESC', 'limit'=>array(0, $this->_last_tpl_number)));

		preg_match_all("#{{[^}]+}}#", $this->_last_tpl_code, $matches);
		$items = array();
		foreach($objs as $obj) {
			$items[] = $this->parseTemplate($obj, $this->_last_tpl_code, $matches);
		}

		$archive = "<a href=\"".$this->_plink->aLink($this->_instanceName, 'archive')."\">"._('elenco completo')."</a>";

		$view = new view($this->_view_dir);

		$view->setViewTpl('last');
		$view->assign('section_id', 'last_pressReview_'.$this->_instanceName);
		$view->assign('title', $this->_title_last);
		$view->assign('feed', "<a href=\"".$this->_plink->aLink($this->_instanceName, 'feedRSS')."\">".pub::icon('feed')."</a>");
		$view->assign('items', $items);
		$view->assign('archive', $archive);

		return $view->render();

	}

	/**
	 * Front end archivio rassegna stampa 
	 * 
	 * @access public
	 * @return lista articoli rassegna stampa
	 */
	public function archive() {

		$this->setAccess($this->_access_base);

		$registry = registry::instance();
		$session = session::instance();

		$this->sessionSearch();

		$where_arr = array();
		if($session->{'pressReviewSearch'.$this->_instance}['newspaper']) {
			$where_arr[] = "newspaper='".$session->{'pressReviewSearch'.$this->_instance}['newspaper']."'";
		}
		if($session->{'pressReviewSearch'.$this->_instance}['text']) {
			$where_arr[] = "(title LIKE '%".$session->{'pressReviewSearch'.$this->_instance}['text']."%' OR notes LIKE '%".$session->{'pressReviewSearch'.$this->_instance}['text']."%')";
		}
		if($session->{'pressReviewSearch'.$this->_instance}['date_from']) {
			$where_arr[] = "date >= '".dateToDbDate($session->{'pressReviewSearch'.$this->_instance}['date_from'], '/')."'";
		}
		if($session->{'pressReviewSearch'.$this->_instance}['date_to']) {
			$where_arr[] = "date <= '".dateToDbDate($session->{'pressReviewSearch'.$this->_instance}['date_to'], '/')."'";
		}

		$where = count($where_arr) ? implode(' AND ', $where_arr) : '';

		$prs_number = pressReviewItem::getCount($this, array('where'=>$where));

		$pagination = new pagelist($this->_list_tpl_ifp, $prs_number, 'array');
		$limit = array($pagination->start(), $this->_list_tpl_ifp);

		$objs = pressReviewItem::get($this, array('where'=>$where, 'order'=>'date DESC', 'limit'=>$limit));

		preg_match_all("#{{[^}]+}}#", $this->_list_tpl_code, $matches);
		$items = array();
		foreach($objs as $obj) {
			$items[] = $this->parseTemplate($obj, $this->_list_tpl_code, $matches);
		}


		$view = new view($this->_view_dir);

		$view->setViewTpl('archive');
		$view->assign('section_id', 'archive_pressReview_'.$this->_instanceName);
		$view->assign('form_search', $this->formSearch());
		$view->assign('title', $this->_title_list);
		$view->assign('feed', "<a href=\"".$this->_plink->aLink($this->_instanceName, 'feedRSS')."\">".pub::icon('feed')."</a>");
		$view->assign('items', $items);
		$view->assign('pagination_summary', $pagination->reassumedPrint());
		$view->assign('pagination_navigation', $pagination->listReferenceGINO($this->_plink->aLink($this->_instanceName, 'archive', '', '', array("basename"=>false))));

		return $view->render();

	}

	/**
	 * Imposta la ricerca in sessione 
	 * 
	 * @access public
	 * @return void
	 */
	private function sessionSearch() {

		$session = session::instance();

		if(isset($_POST['submit_search_all'])) {
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

		if(isset($_POST['submit_search'])) {
			if(isset($_POST['search_newspaper'])) { 
				$search['newspaper'] = cleanVar($_POST, 'search_newspaper', 'int', '');
			}
			if(isset($_POST['search_text'])) { 
				$search['text'] = cleanVar($_POST, 'search_text', 'string', '');
			}
			if(isset($_POST['search_from'])) { 
				$search['date_from'] = cleanVar($_POST, 'search_from', 'string', '');
			}
			if(isset($_POST['search_to'])) { 
				$search['date_to'] = cleanVar($_POST, 'search_to', 'string', '');
			}
			$session->{'pressReviewSearch'.$this->_instance} = $search;
		}

	}

	/**
	 * Form di ricerca in archivio 
	 * 
	 * @access public
	 * @return form ricerca archivio
	 */
	private function formSearch() {
		
		$session = session::instance();

		$myform = new form('form_search_pr', 'post', false);
		$form_search = $myform->form($this->_plink->aLink($this->_instanceName, 'archive', array()), false, '');
		$form_search .= $myform->cselect('search_newspaper', $session->{'pressReviewSearch'.$this->_instance}['newspaper'], pressNewspaper::getForSelect($this), _('Testata'), array());
		$form_search .= $myform->cinput_date('search_from', $session->{'pressReviewSearch'.$this->_instance}['date_from'], _('Da'), array());
		$form_search .= $myform->cinput_date('search_to', $session->{'pressReviewSearch'.$this->_instance}['date_to'], _('A'), array());
		$form_search .= $myform->cinput('search_text', 'text', htmlInput($session->{'pressReviewSearch'.$this->_instance}['text']), _('Titolo/Note'), array('size'=>20, 'maxlength'=>40));
		$submit_all = $myform->input('submit_search_all', 'submit', _('tutti'), array('classField'=>'submit'));
		$form_search .= $myform->cinput('submit_search', 'submit', _('cerca'), '', array('classField'=>'submit', 'text_add'=>' '.$submit_all));
		$form_search .= $myform->cform();

		return $form_search;
	}


	/**
	 * Parserizzazione dei template inseriti da opzioni 
	 * 
	 * @param newsItem $item istanza di pressReviewItem
	 * @param string $tpl codice del template 
	 * @param array $matches matches delle variabili da sostituire
	 * @return template parserizzato
	 */
	private function parseTemplate($item, $tpl, $matches) {

		if(isset($matches[0])) {
			foreach($matches[0] as $m) {
				$code = trim(preg_replace("#{|}#", "", $m));
				if($pos = strrpos($code, '|')) {
					$property = substr($code, 0, $pos);
					$filter = substr($code, $pos + 1);
				}
				else {
					$property = $code;
					$filter = null;
				}

				$replace = $this->replaceTplVar($property, $filter, $item);
				$tpl = preg_replace("#".preg_quote($m)."#", $replace, $tpl);
			} 
		}

		return $tpl;
	}

	/**
	 * Replace di una proprietà di pressReviewItem all'interno del template 
	 * 
	 * @param string $property proprietà da sostituire
	 * @param string $filter filtro applicato
	 * @param newsItem $obj istanza di pressReviewItem
	 * @return replace del parametro proprietà
	 */
	private function replaceTplVar($property, $filter, $obj) {

		$pre_filter = '';

		if($property == 'newspaper') {
			$pn = new pressNewspaper($obj->newspaper, $this);
			$link = preg_match("#http://#", $pn->link) ? $pn->link : "http://".$pn->link;
			if($pn->link) {
				$pre_filter = "<a href=\"".$link."\">".htmlCHars($pn->ml('name'))."</a>";
			}
			else {
				$pre_filter = htmlChars($pn->ml('name'));
			}
		}
		elseif($property == 'newspaper_logo') {
			$pn = new pressNewspaper($obj->newspaper, $this);
			if(!$pn->logo) return '';
			$pre_filter = "<img src=\"".$pn->logoPath($this)."\" alt=\"".jsVar($obj->ml('name'))."\" />";	
		}
		elseif($property == 'date') {
			$pre_filter = date('d/m/Y', strtotime($obj->{$property}));
		}
		elseif($property == 'social') {

			$title_site = pub::variable('head_title');
			$title = $title_site.($this->_title_list ? " - ".$this->_title_list : "");

			if($obj->file) {
				$link = $this->_url_root.SITE_WWW."/".$this->_plink->aLink($this->_instanceName, 'download', array("id"=>$obj->id));
			}	
			else {
				$link = preg_match("#http://#", $obj->link) ? $obj->link : "http://".$obj->link;
			}

			$pre_filter = shareAll('all', $link, $title);
		}
		elseif($property == 'notes' || $property == 'title') {
			$pre_filter = htmlChars($obj->ml($property));
		}
		else {
			return '';
		}

		if(is_null($filter)) {
			return $pre_filter;
		}

		if($filter == 'link') {
			if($obj->file) {
				return "<a href=\"".$this->_plink->aLink($this->_instanceName, 'download', array('id'=>$obj->id))."\">".$pre_filter."</a>";
			}
			else {
				$link = preg_match("#http://#", $obj->link) ? $obj->link : "http://".$obj->link;
				return "<a rel=\"external\" href=\"".$link."\">".$pre_filter."</a>";
			}
		}
		elseif(preg_match("#class:(.+)#", $filter, $matches)) {
			if(isset($matches[1]) && $property == 'newspaper_logo') {
				return preg_replace("#<img#", "<img class=\"".$matches[1]."\"", $pre_filter);
			}
			else return $pre_filter;
		}
		else {
			return $pre_filter;
		}

	}
	
	/**
	 * Interfaccia di amministrazione del modulo 
	 * 
	 * @return interfaccia di back office
	 */
	public function manageDoc() {

		$this->accessGroup('ALL');

		$method = 'manageDoc';

		$htmltab = new htmlTab(array("linkPosition"=>'right', "title"=>$this->_instanceLabel));	
		$link_admin = "<a href=\"".$this->_home."?evt[$this->_instanceName-$method]&block=permissions\">"._("Permessi")."</a>";
		$link_css = "<a href=\"".$this->_home."?evt[$this->_instanceName-$method]&block=css\">"._("CSS")."</a>";
		$link_options = "<a href=\"".$this->_home."?evt[$this->_instanceName-$method]&block=options\">"._("Opzioni")."</a>";
		$link_newspaper = "<a href=\"".$this->_home."?evt[$this->_instanceName-$method]&block=newspaper\">"._("Testate")."</a>";
		$link_dft = "<a href=\"".$this->_home."?evt[".$this->_instanceName."-$method]\">"._("Rassegne stampa")."</a>";

		$sel_link = $link_dft;

		if($this->_block == 'css') {
			$buffer = sysfunc::manageCss($this->_instance, $this->_className);		
			$sel_link = $link_css;
		}
		elseif($this->_block == 'permissions' && $this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', '')) {
			$buffer = sysfunc::managePermissions($this->_instance, $this->_className);		
			$sel_link = $link_admin;
		}
		elseif($this->_block == 'options' && $this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', '')) {
			$buffer = sysfunc::manageOptions($this->_instance, $this->_className);		
			$sel_link = $link_options;
		}
		elseif($this->_block == 'newspaper') {
			$buffer = $this->manageNewspaper();		
			$sel_link = $link_newspaper;
		}
		else {
			$buffer = $this->managePressReview();
		}

		// groups privileges
		if($this->_access->AccessVerifyGroupIf($this->_className, $this->_instance, '', '')) {
			$links_array = array($link_admin, $link_css, $link_options, $link_newspaper, $link_dft);
		}
		else $links_array = array($link_newspaper, $link_dft);

		$htmltab->navigationLinks = $links_array;
		$htmltab->selectedLink = $sel_link;
		$htmltab->htmlContent = $buffer;

		return $htmltab->render();

	}

	/**
	 * Backoffice articoli rassegna stampa 
	 * 
	 * @return interfaccia di back office degli articoli della rassegna stampa
	 */
	private function managePressReview() {
		
		$registry = registry::instance();

		$admin_table = new adminTable($this, array());

		$buffer = $admin_table->backOffice(
			'pressReviewItem', 
			array(
				'list_display' => array('id', 'newspaper', 'title', 'date', 'file', 'link', 'notes'),
				'list_title'=>_("Elenco articoli"), 
				'filter_fields'=>array('newspaper', 'title', 'notes') 
			     ),
			array(
			), 
			array(
			)
		);

		return $buffer;
		
	}

	/**
	 * Backoffice testate 
	 * 
	 * @return interfaccia di back office delle testate
	 */
	private function manageNewspaper() {
		
		$registry = registry::instance();

		$admin_table = new adminTable($this, array());

		$buffer = $admin_table->backOffice(
				'pressNewspaper', 
				array(
					'list_display' => array('id', 'name', 'logo', 'link'),
					'list_title'=>_("Elenco testate") 
				     ),
				array(), 
				array(
					'logo' => array(
						'preview' => true
					)
				)
		);

		return $buffer;
	}

	/**
	 * Metodo per la definizione di parametri da utilizzare per il modulo "Ricerca nel sito"
	 *
	 * Il modulo "Ricerca nel sito" di Gino base chiama questo metodo per ottenere informazioni riguardo alla tabella, campi, pesi etc...
	 * per effettuare la ricerca dei contenuti.
	 *
	 * @access public
	 * @return array[string]mixed array associativo contenente i parametri per la ricerca
	 */
	public function searchSite() {

		return array(
			"table"=>pressReviewItem::$tbl_item, 
			"selected_fields"=>array("id", "date", array("highlight"=>true, "field"=>"title"), array("highlight"=>true, "field"=>"notes")), 
			"required_clauses"=>array("instance"=>$this->_instance), 
			"weight_clauses"=>array("title"=>array("weight"=>3), "notes"=>array("weight"=>1))
		);
	}

	/**
	 * Definisce la presentazione del singolo item trovato a seguito di ricerca (modulo "Ricerca nel sito")
	 *
	 * @param mixed array array[string]string array associativo contenente i risultati della ricerca
	 * @access public
	 * @return void
	 */
	public function searchSiteResult($results) {
	
		$obj = new pressReviewItem($results['id'], $this);

		$link = '';
		if($obj->file) {
			$link = $this->_url_root.SITE_WWW."/".$this->_plink->aLink($this->_instanceName, 'download', array("id"=>$obj->id));
		}	
		else {
			$link = preg_match("#http://#", $obj->link) ? $obj->link : "http://".$obj->link;
		}

		$buffer = "<div>".dbDatetimeToDate($results['date'], "/")." <a href=\"".$link."\">";
		$buffer .= $results['title'] ? htmlChars($results['title']) : htmlChars($obj->ml('title'));
		$buffer .= "</a></div>";

		if($results['notes']) $buffer .= "<div class=\"search_text_result\">...".htmlChars($results['notes'])."...</div>";
		
		return $buffer;

	}

	/**
	 * Genera un feed RSS standard che presenta gli ultimi 50 articoli della rassegna stampa
	 *
	 * @access public
	 * @return string xml che definisce il feed RSS
	 */
	public function feedRSS() {

		$this->accessType($this->_access_base);

		header("Content-type: text/xml; charset=utf-8");

		$function = "feedRSS";
		$title_site = pub::variable('head_title');
		$title = $title_site.($this->_title_list ? " - ".$this->_title_list : "");
		$description = $this->_db->getFieldFromId(TBL_MODULE, 'description', 'id', $this->_instance);

		$header = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		$header .= "<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n";
		$header .= "<channel>\n";
		$header .= "<atom:link href=\"".$this->_url_root.$this->_home."?pt%5B$this->_instanceName-".$function."%5D\" rel=\"self\" type=\"application/rss+xml\" />\n";
		$header .= "<title>".$title."</title>\n";
		$header .= "<link>".$this->_url_root.$this->_home."</link>\n";
		$header .= "<description>".$description."</description>\n";
		$header .= "<language>$this->_lng_nav</language>";
		$header .= "<copyright> Copyright 2012 Otto srl </copyright>\n";
		$header .= "<docs>http://blogs.law.harvard.edu/tech/rss</docs>\n";

		echo $header;

		$prs = pressReviewItem::get($this, array('order'=>'date DESC', 'limit'=>array(0, 50)));
		if(count($prs) > 0) {
			foreach($prs as $pr) {
				$id = htmlChars($pr->id);
				$title = htmlChars($pr->ml('title'));
				$text = htmlChars($pr->ml('notes'));
				$text = str_replace("src=\"", "src=\"".substr($this->_url_root,0,strrpos($this->_url_root,"/")), $text);
				$text = str_replace("href=\"", "href=\"".substr($this->_url_root,0,strrpos($this->_url_root,"/")), $text);

				$date = date('d/m/Y', strtotime($n->date));
				
				$link = '';
				if($pr->file) {
					$link = $this->_url_root.SITE_WWW."/".$this->_plink->aLink($this->_instanceName, 'download', array("id"=>$pr->id));
				}	
				else {
					$link = preg_match("#http://#", $pr->link) ? $pr->link : "http://".$pr->link;
				}

				echo "<item>\n";
				echo "<title>".$date.". ".$title."</title>\n";
				echo "<link>".$link."</link>\n";
				echo "<description>\n";
				echo "<![CDATA[\n";
				echo $text;
				echo "]]>\n";
				echo "</description>\n";
				echo "<guid>".$link."</guid>\n";
				echo "</item>\n";
			}
		}

		$footer = "</channel>\n";
		$footer .= "</rss>\n";

		echo $footer;
		exit;
	}


}
