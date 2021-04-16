<?php
namespace app\widgets;

use Yii;

use yii\base\Widget;
use yii\helpers\Html;

class Alphabet extends Widget
{
    public $letters;

    public function init()
    {
        parent::init();
        if ($this->letters === null) {
            $this->letters = range('A', 'Z');
        }
    }

    public function run()
    {
        return $this->render('alphabet.twig', ['letters' => $this->letters]);
    }
}