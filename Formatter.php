<?php

/**
 * @copyright Copyright &copy; Samir IZZA, 2015
 * @package yii2-helpers
 * @version 1.0.0
 */

namespace vendor\samirmember\helpers;


use yii\i18n\Formatter as BaseFormatter;
use Yii;
use DateTimeZone;

/**
 * Add some formatter to the Yii Formatter. The goal is to use them in the DetailView
 *
 * @author Samir IZZA <samirmember@gmail.com>
 * @since 1.0
 * @see https://dz.linkedin.com/pub/samir-izza/36/9a6/4a
 */
class Formatter extends BaseFormatter
{

    private $_intlLoaded = false;

    private function formatDateTimeValue($value, $format, $type)
    {
        $timeZone = $this->timeZone;
        // avoid time zone conversion for date-only values
        if ($type === 'date') {
            list($timestamp, $hasTimeInfo) = $this->normalizeDatetimeValue($value, true);
            if (!$hasTimeInfo) {
                $timeZone = $this->defaultTimeZone;
            }
        } else {
            $timestamp = $this->normalizeDatetimeValue($value);
        }
        if ($timestamp === null) {
            return $this->nullDisplay;
        }

        // intl does not work with dates >=2038 or <=1901 on 32bit machines, fall back to PHP
        $year = $timestamp->format('Y');
        if ($this->_intlLoaded && !(PHP_INT_SIZE == 4 && ($year <= 1901 || $year >= 2038))) {
            if (strncmp($format, 'php:', 4) === 0) {
                $format = \yii\helpers\FormatConverter::convertDatePhpToIcu(substr($format, 4));
            }
            if (isset($this->_dateFormats[$format])) {
                if ($type === 'date') {
                    $formatter = new \yii\i18n\IntlDateFormatter($this->locale, $this->_dateFormats[$format],\yii\i18n\IntlDateFormatter::NONE, $timeZone);
                } elseif ($type === 'time') {
                    $formatter = new \yii\i18n\IntlDateFormatter($this->locale, \yii\i18n\IntlDateFormatter::NONE, $this->_dateFormats[$format], $timeZone);
                } else {
                    $formatter = new \yii\i18n\IntlDateFormatter($this->locale, $this->_dateFormats[$format], $this->_dateFormats[$format], $timeZone);
                }
            } else {
                $formatter = new \yii\i18n\IntlDateFormatter($this->locale, \yii\i18n\IntlDateFormatter::NONE, \yii\i18n\IntlDateFormatter::NONE, $timeZone, null, $format);
            }
            if ($formatter === null) {
                throw new \yii\base\InvalidConfigException(intl_get_error_message());
            }
            // make IntlDateFormatter work with DateTimeImmutable
            if ($timestamp instanceof \DateTimeImmutable) {
                $timestamp = new \yii\i18n\DateTime($timestamp->format(\yii\i18n\DateTime::ISO8601), $timestamp->getTimezone());
            }
            return $formatter->format($timestamp);
        } else {
            if (strncmp($format, 'php:', 4) === 0) {
                $format = substr($format, 4);
            } else {
                $format = \yii\helpers\FormatConverter::convertDateIcuToPhp($format, $type, $this->locale);
            }
            if ($timeZone != null) {
                if ($timestamp instanceof \DateTimeImmutable) {
                    $timestamp = $timestamp->setTimezone(new DateTimeZone($timeZone));
                } else {
                    $timestamp->setTimezone(new DateTimeZone($timeZone));
                }
            }
            return $timestamp->format($format);
        }
    }
    /**
     * Formats the value as a color badge.
     * @param string $value the value to be formatted. Must be in the format of '#xxxxxx'
     * @param boolean $showHex whether to display the hexa value. Default to false.
     * @return string the formatted result.
     */
    public function asColor($value, $showHex = false)
    {
        if ($value == null) return $this->nullDisplay;
        //init
        $this->dateFormat = Yii::$app->formatter->dateFormat;
        $this->datetimeFormat = Yii::$app->formatter->datetimeFormat;
        $this->decimalSeparator = Yii::$app->formatter->decimalSeparator;
        $this->thousandSeparator = Yii::$app->formatter->thousandSeparator;
        $this->currencyCode = Yii::$app->formatter->currencyCode;
        $this->timeZone = Yii::$app->formatter->timeZone;

        //generating content
        $content = "";
        if ($showHex) {
            switch ($value) {
                case '#ffffff':
                    $nom = "Blanc";
                    break;
                case '#000000':
                    $nom = "Noir";
                    break;
                case '#808080':
                    $nom = "Gris";
                    break;
                case '#c0c0c0':
                    $nom = "Argent";
                    break;
                case '#ffd700':
                    $nom = "Doré";
                    break;
                case '#a52a2a':
                    $nom = "Marron";
                    break;
                case '#ff0000':
                    $nom = "Rouge";
                    break;
                case '#ffa500':
                    $nom = "Orange";
                    break;
                case '#ffff00':
                    $nom = "Jaune";
                    break;
                case '#4b0082':
                    $nom = "Indigo";
                    break;
                case '#800000':
                    $nom = "Bordeaux";
                    break;
                case '#ffc0cb':
                    $nom = "Rose";
                    break;
                case '#0000ff':
                    $nom = "Bleu";
                    break;
                case '#008000':
                    $nom = "Vert";
                    break;
                case '#ee82ee':
                    $nom = "Violet";
                    break;
                case '#00ffff':
                    $nom = "Cyan";
                    break;
                case '#ff00ff':
                    $nom = "Magenta";
                    break;
                case '#800080':
                    $nom = "Pourpre";
                    break;
                default:
                    $nom = "Autre";
            }
            $content = "&nbsp;<code>" . $nom . '</code>';
        }
        return \yii\helpers\Html::tag("span", "&nbsp;",["class"=>"badge", "style"=>"background-color: $value; border: 1px solid grey;"]) . $content;
    }

    public function asUser($value)
    {
        $user = \dektrium\user\models\User::findOne($value);
        if (!$user) return $this->nullDisplay;
        $profile = \dektrium\user\models\Profile::findOne($value);

        if ($profile) {
            if ($profile->name) $name = $profile->name.' ('.$user->username.')';
            else $name = $user->username;
        } else $name = $user->username;
        if (Yii::$app->user->can('/user/admin/update')) {
            $url = \yii\helpers\Url::to(['../user/admin/update', 'id' => $value]);
            $result = \yii\helpers\Html::a($name, $url, ['target'=>'_blank']);
        } else {
            $result = $name;
        }
        return $result;
    }

    public function asLink($value, $className, $attributeName = Null, $link = true)
    {
        if ($className != 'User') $modelName = 'backend\models\\'.$className;
        else $modelName = 'common\models\User';
        $model = $modelName::findOne($value);
        if ($className != 'User') { 
            if (!$model) 
                return $this->nullDisplay; 
        } else if (!$model) return "<span class=\"not-set\">(Aucun)</span>";

        if ($className != 'User') {
            if (!$attributeName) $attributeName = 'nom_'.strtolower($className);
            $route = '/'.lcfirst($className).'/view';
        } else {
            $attributeName = 'username';
            $route = '/user/admin/update';
        }
        $name = $model->{$attributeName};

        $classRoute = lcfirst($className);
        if ((Yii::$app->user->can("$route"))&&($link)) {
            $url = yii\helpers\Url::to(["$route", 'id' => $value]);
            $result = \yii\helpers\Html::a($name, $url, ['target'=>'_blank', 'data-pjax'=>"0"]);
        } else {
            $result = $name;
        }
        return $result;
    }

    public function asLinkToModel($value, $classLabel, $attributeLabel = NULL)
    {
        $class = Yii::$app->controller->id;
        $modelName = 'backend\models\\'.ucfirst($class);
        $model = $modelName::findOne($value);
        // globals $data;
        var_dump($data);
        exit();

        if (!$attributeLabel) $attributeLabel = 'nom_'.strtolower($classLabel);
        $name = $model->{'id'.$classLabel}->{$attributeLabel};

        $route = '/'.$class.'/view';
        if (Yii::$app->user->can("$route")) {
            $url = yii\helpers\Url::to(["$route", 'id' => $model->id]);
            $result = \yii\helpers\Html::a($name, $url, ['data-pjax'=>"0"]);
        } else {
            $result = $name;
        }
        return $result;
    }

    public function asFile($value, $attribute = NULL)
    {
        // if (!$value) return null;
        if(!$attribute) {
            $callers=debug_backtrace();
            if(isset($callers['3']['object']->attribute)) { // if from index
                // $attribute = $callers['3']['object']->attribute;
                // $type = substr($attribute, 8, strlen($attribute)); // remove fichier_
                $type = str_replace('-', '_', Yii::$app->controller->id);
                if ($value)
                    return \yii\helpers\Html::a(
                        \kartik\icons\Icon::show('file-text-o'),
                        Yii::$app->urlManager->baseUrl.Yii::$app->params['fileUploadPath'] . $type .'/'. $value,
                        ['target'=>'_blank', 'data-pjax'=>"0"]
                    );
            } else { // else from view
                $type = Yii::$app->functions->getClassName($callers['3']['object']->model);
            }
        } else {
            $type = substr($attribute, 8, strlen($attribute)); // remove fichier_
        }

        return \yii\helpers\Html::a(
            $value, 
            Yii::$app->urlManager->baseUrl.Yii::$app->params['fileUploadPath'] . $type .'/'. $value,
            ['target'=>'_blank']
        );
    }

    public function asBadgeBoolean($value, $on = NULL, $off = NULL)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        if ($value == '1') {
            if (!$on) $word = 'Activé'; else $word = $on;
            $class = 'success';
        } else if ($value == '0') {
            if (!$off) $word = 'Désactivé'; else $word = $off;
            $class = 'danger';
        } else if ($value == '-1') {
            $word = 'En attente';
            $class = 'warning';
        } else if ($value == '-2') {
            $word = 'Pas encore validé';
            $class = 'default';
        }

        return \yii\helpers\Html::tag("span", $word,["class"=>"label label-".$class." etat"]);
        // return $value ?  : \yii\helpers\Html::tag("span", $this->booleanFormat[0],["class"=>"label label-danger etat"]);
        // return $value ? \yii\helpers\Html::tag("span", $this->booleanFormat[1],["class"=>"label label-success etat"]) : \yii\helpers\Html::tag("span", $this->booleanFormat[0],["class"=>"label label-danger etat"]);
    }

    public function asPhoto($value, $type, $options = [], $data = false)
    {
        if (($value === null)||($value == '')) {
            // return $this->nullDisplay;
            return yii\helpers\Html::img(Yii::$app->urlManager->baseUrl . Yii::$app->params['imageUploadPath'] . 'no-image-thumb.png', $options);
        }
        $url = Yii::getAlias('@baseurl') . "/" . Yii::$app->params['imageUploadPath'] . $type . '/thumbs/' . $value;
        // if ($data) $url = Yii::$app->functions->image2data($url);
        // var_dump($url);
        // exit();
        return \yii\helpers\Html::img($url, $options);
    }

    public function asTriState($value)
    {
        if (!in_array($value, ['1', '-1', '0'])) throw new \yii\base\UserException('La valeur de l\'état de l\'objet n\'est pas prise en charge');
        if ($value == '1') $color = 'vert';
        else if ($value == '0') $color = 'rouge';
        else if ($value == '-1') $color = 'orange';
        return \yii\helpers\Html::tag("span", "&nbsp;",["class"=>"cercle ".$color]);
    }

    public function asDatePresume($value, $format = null)
    {
        if ($format === null) {
            $format = 'medium';
        }

        if (substr($value, -2) == '00') return Yii::$app->functions->presumeDate($value);
        else return $this->formatDateTimeValue($value, $format, 'date');
    }

    public function asDatetime($value, $format = null)
    {
        $this->timeZone = NULL;
        if ($format === null) {
            $format = $this->datetimeFormat;
        }
        return $this->formatDateTimeValue($value, $format, 'datetime');
    }
}