<?php
/**
 * \file class.pressReviewItem.php
 * Contiene la definizione ed implementazione della classe pressReviewItem.
 * 
 * @version 0.1
 * @copyright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */

/**
 * \ingroup gino-pressReview
 * Classe tipo model che rappresenta una rassegna stampa.
 *
 * @version 0.1
 * @copyright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
class pressReviewItem extends propertyObject {

	private $_controller;

	protected static $_extension_file = array('pdf', 'doc', 'xdoc', 'odt');
	public static $tbl_item = 'press_review_item';

	/**
	 * Costruttore
	 * 
	 * @param integer $id valore ID del record
	 * @param object $instance istanza del controller
	 */
	function __construct($id, $instance) {

		$this->_controller = $instance;
		$this->_tbl_data = self::$tbl_item;

		$this->_fields_label = array(
			'newspaper'=>_('Testata'),
			'title'=>_('Titolo'),
			'date'=>_('Data'),
			'file'=>_('File'),
			'link'=>_('Link assoluto'),
			'notes'=>_("Note")
		);

		parent::__construct($id);

		$this->_model_label = $this->id ? $this->title : '';
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

		$structure['newspaper'] = new foreignKeyField(array(
			'required'=>true,
			'name'=>'newspaper', 
			'value'=>$this->newspaper, 
			'label'=>$this->_fields_label['newspaper'], 
			'lenght'=>255, 
			'fkey_table'=>pressNewspaper::$tbl_newspaper, 
			'fkey_id'=>'id', 
			'fkey_field'=>'name', 
			'fkey_where'=>'instance=\''.$this->_controller->getInstance().'\'', 
			'fkey_order'=>'name',
			'table'=>$this->_tbl_data 
		));
		
		$base_path = $this->_controller->getBaseAbsPath('papers');

		$structure['file'] = new fileField(array(
                        'name'=>'file', 
                        'value'=>$this->file, 
                        'label'=>$this->_fields_label['file'], 
                        'lenght'=>100, 
                        'extensions'=>self::$_extension_file, 
                        'path'=>$base_path,
			'check_type'=>false 
                ));

		return $structure;
	}

	/**
	 * Restituisce oggetti di tipo pressReviewItem 
	 * 
	 * @param multimedia $controller istanza del controller 
	 * @param array $options array associativo di opzioni 
	 * @return array di istanze di tipo pressReviewItem
	 */
	public static function get($controller, $options = null) {

		$res = array();

		$where_q = gOpt('where', $options, '');
		$order = gOpt('order', $options, 'name');
		$limit = gOpt('limit', $options, null);

		$db = db::instance();
		$selection = 'id';
		$table = self::$tbl_item;
		$where_arr = array("instance='".$controller->getInstance()."'");
		if($where_q) {
			$where_arr[] = $where_q;
		} 
		$where = implode(' AND ', $where_arr);

		$rows = $db->select($selection, $table, $where, $order, $limit);
		if(count($rows)) {
			foreach($rows as $row) {
				$res[] = new pressReviewItem($row['id'], $controller);
			}
		}

		return $res;

	}

	/**
	 * Restituisce il numero di articoli della rassegna stampa che soddisfano le condizioni date 
	 * 
	 * @param multimedia $controller istanza del controller 
	 * @param array $options array associativo di opzioni 
	 * @return numero di articoli
	 */
	public static function getCount($controller, $options = null) {

		$res =0;

		$where_q = gOpt('where', $options, '');

		$db = db::instance();
		$selection = 'COUNT(id) AS tot';
		$table = self::$tbl_item;
		$where_arr = array("instance='".$controller->getInstance()."'");
		if($where_q) {
			$where_arr[] = $where_q;
		} 
		$where = implode(' AND ', $where_arr);

		$rows = $db->select($selection, $table, $where, null, null);

		if($rows && count($rows)) {
			$res = $rows[0]['tot'];
		}

		return $res;

	}

	
}

?>
