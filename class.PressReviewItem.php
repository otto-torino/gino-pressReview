<?php
/**
 * @file class.PressReviewItem.php
 * @brief Contiene la definizione ed implementazione della classe Gino.App.PressReview.PressReviewItem
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti
 * @author abidibo
 */

namespace Gino\App\PressReview;

use \Gino\Db;
use \Gino\ForeignKeyField;
use \Gino\FileField;

/**
 * @brief Classe di tipo Gino.Model che rappresenta un item della rassegna stampa
 * @copyright 2014 Otto srl (http://www.opensource.org/licenses/mit-license.php) The MIT License
 * @author marco guidotti
 * @author abidibo
 * @version 0.1.0
 */
class PressReviewItem extends \Gino\Model
{
    public static $table = 'press_review_item';
    protected static $_extension_file = array('pdf', 'doc', 'docx', 'odt', 'jpg', 'jpeg', 'tiff', 'tif');

    /**
     * @brief Costruttore
     *
     * @param int $id id del record
     * @param \Gino\App\PressReview\pressReview $instance
     * @return istanza di Gino.App.PressReview.PressReviewItem
     */
    public function __construct($id, $instance)
    {
        $this->_controller = $instance;
        $this->_tbl_data = self::$table;

        $this->_fields_label = array(
            'newspaper' => _('testata'),
            'title' => _('titolo'),
            'date' => _('data'),
            'file' => _('file'),
            'link' => _('link'),
            'notes' => _('note'),
        );

        parent::__construct($id);

        $this->_model_label = _('Articolo');
    }

    /**
     * @brief Rappresentazione a stringa dell'oggetto
     *
     * @return titolo
     */
    function __toString()
    {
        return (string) $this->title;
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

        $structure['newspaper'] = new ForeignKeyField(array(
            'name' => 'newspaper',
            'model' => $this,
            'required' => TRUE,
            'foreign'=>'\Gino\App\PressReview\PressNewspaper',
            'foreign_where'=>'instance=\''.$this->_controller->getInstance().'\'',
            'foreign_controller'=>$this->_controller,
            'add_related' => TRUE,
            'add_related_url' => $this->_controller->linkAdmin(array(), 'block=newspaper&insert=1')
        ));

        $base_path = $this->_controller->getTypeBaseAbsPath('papers');
        $structure['file'] = new FileField(array(
            'name'=>'file',
            'model' => $this,
            'extensions'=>self::$_extension_file,
            'path'=>$base_path,
            'check_type'=>FALSE
        ));

        return $structure;
    }

    /**
     * @brief Restituisce il numero di articoli della rassegna stampa che soddisfano le condizioni date
     *
     * @param \Gino\App\PressReview\pressReview $controller istanza del controller
     * @param array $options array associativo di opzioni
     * @return numero di articoli
     */
    public static function getCount(\Gino\App\PressReview\pressReview $controller, $options = null) {

        $res =0;
        $where_q = \Gino\gOpt('where', $options, '');

        $db = Db::instance();
        $where_arr = array("instance='".$controller->getInstance()."'");
        if($where_q) {
            $where_arr[] = $where_q;
        }
        $where = implode(' AND ', $where_arr);

        return $db->getNumRecords(self::$table, $where);

    }

    /**
     * @brief Url della risorsa
     *
     * La risorsa puo' essere un link ad un articolo esterno oppure un file uploadato
     *
     * @param bool $abs se TRUE restituisce il percorso assoluto per il download del file
     * @return url
     */
    public function resourceUrl($abs = FALSE)
    {
        if($this->file) {
            return $this->_controller->link($this->_controller->getInstanceName(), 'download', array('id' => $this->id), array(), array('abs' => $abs));
        }
        else {
            return preg_match("#http://#", $this->link) ? $this->link : "http://".$this->link;
        }
    }

}
