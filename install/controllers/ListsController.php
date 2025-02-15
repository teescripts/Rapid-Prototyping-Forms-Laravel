<?php
namespace App\Http\Controllers;

use Teescripts\RptForms\lists;

final class ListsController extends Controller
{
    public $model;
    function __construct() {
        $this->model=new lists();
    }

    # ---- references
    function all($type="array", $name="", $args="") {
	    $result	=$this->$type($name, $args);
        return $result;
    }

    function index($name="", $args="") {
        return $this->array($module, $args);
    }

    function array($name="", $args="") {
        return $this->model->loadArray($name, $args);
    }

    function query($name="", $args="") {
        return $this->model->as_query($name, $args);
    }

    function item($name="", $args="") {
        return $this->model->loader($name, $args);
    }

    function get($name="", $dataid="") {
        return $this->model->loadView($name, $dataid);
    }

    function view($name="", $dataid="") {
        return $this->model->loadView($name, $dataid);
    }

    function text($name="", $args="") {
        return $this->model->loadText($name, $args);
    }

    function nest($name="", $args="", $type="") {
        return $this->model->loadNest($name, [$args, $type]);
    }

    function json($name="", $args="") {
        $text   =$this->model->loadJson($name, $args);
        return response($text, 200);
    }

    function grid($name="", $args="") {
        $text   =$this->model->loadGrid($name, $args);
        return response($text, 200);
    }

    function select($name="", $args="") {
        $text   =$this->model->loadSelect($name, $args);
        return response($text, 200);
    }

    function suggest($name="", $args="") {
        $text   =$this->model->loadSuggest($name, $args);
        return response($text, 200);
    }

    function nestJson($name="", $args="", $type="") {
        $text   =$this->model->loadNestJson($name, [$args, $type]);
        return response($text, 200);
    }

    function nestGrid($name="", $args="", $type="") {
        $text   =$this->model->loadNestGrid($name, [$args, $type]);
        return response($text, 200);
    }

    function nestSelect($name="", $args="", $type="") {
        $text   =$this->model->loadNestSelect($name, [$args, $type]);
        return response($text, 200);
    }

}
