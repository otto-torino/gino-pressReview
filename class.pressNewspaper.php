<?php
/**
 * \file class.pressNewspaper.php
 * Contiene la definizione ed implementazione della classe pressNewspaper.
 * 
 * @version 0.1
 * @copyright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

/**
 * \ingroup gino-pressReview
 * Classe tipo model che rappresenta una testata giornalistica.
 *
 * @version 0.1
 * @copyright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class pressNewspaper extends propertyObject {

	private $_controller;

	protected static $_extension_img = array('jpg', 'jpeg', 'png');
	public static $tbl_newspaper = 'press_review_newspaper';

	/**
	 * Costruttore
	 * 
	 * @param integer $id valore ID del record
	 * @param object $instance istanza del controller
	 */
	function __construct($id, $instance) {

		$this->_controller = $instance;
		$this->_tbl_data = self::$tbl_newspaper;

		$this->_fields_label = array(
			'name'=>_('Nome'),
			'logo'=>_('Logo'),
			'link'=>_("Link sito web")
		);

		parent::__construct($id);

		$this->_model_label = $this->id ? $this->name : '';
	}

	/**
	 * Sovrascrive la struttura di default
	 * 
	 * @see propertyObject::structure()
	 * @param integer $id
	 * @return array
	 */
	public function structure($id) {
		
		$structure = parent::structure($id);

		$base_path = $this->_controller->getBaseAbsPath('logo');

		$structure['logo'] = new imageField(array(
                        'name'=>'logo', 
                        'value'=>$this->logo, 
                        'label'=>$this->_fields_label['logo'], 
                        'lenght'=>200, 
                        'extensions'=>self::$_extension_img, 
                        'path'=>$base_path, 
                        'resize'=>true,
                        'thumb'=>false,
			'width'=>$this->_controller->getNewspaperLogoWidth()	
                ));

		return $structure;
	}

	/**
	 * Restituisce una lista id=>name da utilizzare per un menu a tendina 
	 * 
	 * @param multimedia $controller istanza del controller 
	 * @param array $options array associativo di opzioni 
	 * @return array associativo id=>name
	 */
	public static function getForSelect($controller, $options = null) {

		$res = array();

		$where_q = gOpt('where', $options, '');
		$order = gOpt('order', $options, 'name');

		$db = db::instance();
		$selection = 'id, name';
		$table = self::$tbl_newspaper;
		$where_arr = array("instance='".$controller->getInstance()."'");
		if($where_q) {
			$where_arr[] = $where_q;
		} 
		$where = implode(' AND ', $where_arr);

		$rows = $db->select($selection, $table, $where, $order, null);
		if(count($rows)) {
			foreach($rows as $row) {
				$res[$row['id']] = htmlChars($row['name']);
			}
		}

		return $res;

	}

	/**
	 * Path relativo del logo
	 * 
	 * @param news $controller istanza del controller
	 * @return path relativo del logo
	 */
	public function logoPath($controller) {

		return $controller->getBasePath('logo').'/'.$this->logo;

	}

}

?>
