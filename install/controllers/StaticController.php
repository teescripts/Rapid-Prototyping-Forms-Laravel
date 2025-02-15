<?php
namespace App\Http\Controllers;

use Teescripts\RptForms\statics;
class StaticController extends Controller
{
    public $model;
    function __construct() {
        $this->model=new lists();
    }

    function all($type="array", $name="", $args="") {
	    return $this->$type($name, $args);
    }
    # ---- static lists
    function index($function="", $args=[]) {
        return $this->array($function, $args);
    }
    function array($module="", $args="") {
        return $this->model->as_array($module, $args);
    }
    function get($module="", $args="") {
        return $this->model->as_get($module, $args);
    }
    function nest($module="", $args="") {
        return $this->model->as_nest($module, $args);
    }
    function tree($module="", $args="") {
        return $this->model->as_tree($module, $args);
    }
    function item($module="", $args="") {
        return $this->model->as_item($module, $args);
    }
    function view($module="", $args="") {
        return $this->model->as_view($query, $args);
    }
    function list($module="", $args="") {
        return $this->model->as_list($module, $args);
    }
    function query($module="", $args="") {
        return $this->model->as_query($module, $args);
    }
    function grid($module="", $args="") {
        return $this->model->as_grid($module, $args);
    }
    function suggest($module="", $args="") {
        return $this->model->as_suggest($module, $args);
    }
    function json($module="", $args="") {
        return $this->model->as_json($module, $args);
    }
    function select($module="", $args="") {
        return $this->model->as_select($module, $args);
    }
    function text($module="", $args="") {
        return $this->model->as_text($module, $args);
    }
    function nestArray($module="", $args="") {
        return $this->model->nestArray($module, $args);
    }
    function nestJson($module="", $args="") {
        return $this->model->nestJson($module, $args);
    }
    function nestGrid($module="", $args="") {
        return $this->model->nestGrid($module, $args);
    }
    function nestSelect($module="", $args="") {
        return $this->model->nestSelect($module, $args);
    }

}
