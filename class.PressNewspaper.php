<?php
/**
 * @file class.PressNewspaper.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.PressReview.PressNewspaper
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti
 * @author abidibo
 */

namespace Gino\App\PressReview;

use \Gino\Db;
use \Gino\ImageField;

/**
 * @brief Classe di tipo Gino.Model che rappresenta un item della rassegna stampa
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti
 * @author abidibo
 * @version 0.1.0
 */
class PressNewspaper extends \Gino\Model {

    public static $table = 'press_review_newspaper';
    protected static $_extension_img = array('jpg', 'jpeg', 'png');

    /**
     * @brief Costruttore
     *
     * @param int $id id del record
     * @param \Gino\App\PressReview\pressReview $instance
     * @return istanza di Gino.App.PressReview.PressNewspaper
     */
    public function __construct($id, $instance)
    {
        $this->_controller = $instance;
        $this->_tbl_data = self::$table;

        $this->_fields_label = array(
            'name' => _('nome'),
            'logo' => _('logo'),
            'link' => _('link url'),
        );

        parent::__construct($id);

        $this->_model_label = _('Testata');
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     *
     * @return nome
     */
    function __toString()
    {
        return (string) $this->name;
    }

    /**
     * @brief Definizione della struttura del modello
     *
     * @see Gino.Model::structure()
     * @param $id id dell'istanza
     * @return array, struttura del modello
     */
    public function structure($id)
    {
        $structure = parent::structure($id);

        $base_path = $this->_controller->getTypeBaseAbsPath('logo');
        $structure['logo'] = new ImageField(array(
            'name'=>'logo',
            'model' => $this,
            'extensions'=>self::$_extension_img,
            'path'=>$base_path,
            'resize'=>TRUE,
            'thumb'=>FALSE,
            'width'=>$this->_controller->getNewspaperLogoWidth()
        ));

        return $structure;
    }

    /**
     * @brief Restituisce una lista id=>name da utilizzare per un input select
     *
     * @param \Gino\App\PressReview\pressReview $controller istanza del controller
     * @param array $options array associativo di opzioni (where, order)
     * @return array associativo id=>name
     */
    public static function getForSelect(\Gino\App\PressReview\pressReview $controller, $options = null) {

        $res = array();

        $where_q = \Gino\gOpt('where', $options, '');
        $order = \Gino\gOpt('order', $options, 'name');

        $db = Db::instance();
        $selection = 'id, name';
        $table = self::$table;
        $where_arr = array("instance='".$controller->getInstance()."'");
        if($where_q) {
            $where_arr[] = $where_q;
        }
        $where = implode(' AND ', $where_arr);

        $rows = $db->select($selection, $table, $where, $order, null);
        if(count($rows)) {
            foreach($rows as $row) {
                $res[$row['id']] = \Gino\htmlChars($row['name']);
            }
        }

        return $res;

    }

    /**
     * @brief Path relativo del logo
     *
     * @return path
     */
    public function logoUrl() {

        return $this->_controller->getTypeBasePath('logo').'/'.$this->logo;
    }

}
