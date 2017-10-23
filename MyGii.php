<?php

/**
 * @copyright Copyright &copy; Samir IZZA, 2015
 * @package yii2-helpers
 * @version 1.0.0
 */

namespace vendor\samirmember\helpers;

use yii\gii\generators\crud\Generator;
use yii\helpers\Inflector;
use yii\helpers\VarDumper;
use yii\helpers\StringHelper;
use yii\db\Schema;


/**
 * Override functions of Gii's functions
 *
 * @author Samir IZZA <samirmember@gmail.com>
 * @since 1.0
 * @see https://dz.linkedin.com/pub/samir-izza/36/9a6/4a
 */
class MyGii extends Generator
{
    public function scanTable($columns)
    {
        $myPk = NULL;
        $myDates = [];
        $myID = [];
        $myBools = [];
        $myPhotos = [];
        $myFiles = [];
        $myColors = [];
        $myNums = [];
        $myStatus = null;
        $myNomPrenom = false;
        $myNom = false;
        $myPrenom = false;
        $myCreateTime = false;
        $myCreatedBy = false;

        foreach ($columns as $column):
            if ((strpos($column->name, 'date') !== false)&&($column->name!='update_time')&&($column->name!='updated_by')) {
                $myDates[] = $column->name;
            } else if ((strpos($column->name, 'id_') !== false) && (!$column->isPrimaryKey)) {
                $myID[] = $column->name;
            } else if (strpos($column->name, 'etat_') !== false) {
                $myBools[] = $column->name;
            } else if (($column->name == 'update_time')||($column->name == 'create_time')) {
                $myCreateTime = true;
            } else if (($column->name == 'updated_by')||($column->name == 'created_by')) {
                $myCreatedBy = true;
            } else if (strpos($column->name, 'photo_') !== false) {
                $myPhotos[] = $column->name;
            } else if (strpos($column->name, 'fichier_') !== false) {
                $myFiles[] = $column->name;
            } else if (strpos($column->name, 'couleur_') !== false) {
                $myColors[] = $column->name;
            } else if (strpos($column->name, 'numero_') !== false) {
                $myNums[] = $column->name;
            }
            if (strpos($column->name, 'prenom_') !== false) $myPrenom = $column->name;
            else if (strpos($column->name, 'nom_') !== false) $myNom = $column->name;
            else if ($column->name == 'username') $myNom = 'username';
            if ($myNom&&$myPrenom) $myNomPrenom = true;

            if (strpos($column->name, 'etat') !== false) {
                $myStatus = $column->name;
            }
            if ($column->isPrimaryKey) $myPk = $column->name;
        endforeach;
        if ($myNom == false) $myNom = str_replace('id_', 'nom_', $myPk);
        return [
            'myPk' => $myPk,
            'myDates' => $myDates,
            'myID' => $myID,
            'MyBools' => $myBools,
            'myPhotos' => $myPhotos,
            'myFiles' => $myFiles,
            'myColors' => $myColors,
            'myNums' => $myNums,
            'myStatus' => $myStatus,
            'myNomPrenom' => $myNomPrenom,
            'myNom' => $myNom,
            'myPrenom' => $myPrenom,
            'myCreateTime' => $myCreateTime,
            'myCreatedBy' => $myCreatedBy,
        ];
    }

    public function checkFormat($attribute, $id = null)
    {
        if ((strpos($attribute, 'date') !== false)&&($attribute!='update_time')&&($attribute!='updated_by')) {
            return 'date';
        } else if (strpos($attribute, 'id_') !== false) {
            if ($id) {
                if ($attribute != $id) return 'id';
                else return 'primary';
            } else return 'id';
            
        } else if (strpos($attribute, 'etat_') !== false) {
            return 'etat';
        } else if (($attribute == 'update_time')||($attribute == 'create_time')) {
            return 'create_time';
        } else if (($attribute == 'updated_by')||($attribute == 'created_by')) {
            return 'created_by';
        } else if (strpos($attribute, 'photo_') !== false) {
            return 'photo';
        } else if (strpos($attribute, 'fichier_') !== false) {
            return 'file';
        } else if (strpos($attribute, 'couleur_') !== false) {
            return 'color';
        } else if (strpos($attribute, 'numero_') !== false) {
            return 'numero';
        } else if (strpos($attribute, 'email') !== false) {
            return 'email';
        } else return 'text';
    }
	
    protected function getClassAttributeName($attribute)
    {
        $type = $this->checkFormat($attribute);
        if ($type == 'id') $j = 3;
        else if ($type == 'etat') $j = 5;
        else $j = 3;
        $className = Inflector::id2camel(substr($attribute, $j));
        $modelClass = str_replace('_', '-', $attribute);
        // $route = substr($modelClass, $j);
        $modelClass = Inflector::id2camel(substr($modelClass, $j));
        
        $name = Inflector::camel2id(substr($attribute, $j));

        if ($className != 'User') {
            $path = 'backend';
        } else {
            $path = 'common';
        }

        // if ($attribute == 'id_club_precedent') {
        //     $className = Inflector::camel2id($modelClass);
        //     $className = explode('-', $className);
        //     $i = count($className)-1;
        //     unset($className[$i]);
        //     $i--;
        //     if (isset($className[$i]) && ($className[$i] == '')) unset($className[$i]);
        //     $className = implode('-', $className);
        //     $className = Inflector::id2camel($className);
        //     var_dump($path.'\models\\'.$modelClass);
        //     var_dump((class_exists($path.'\models\\'.$modelClass)));
        //     exit();
        // }

        $originClassName = $modelClass;
        while (!class_exists($path.'\models\\'.$modelClass)&&($className != '')) {
            // $className = Inflector::id2camel(str_replace('_', '-', $className));
            $className = Inflector::camel2id($modelClass);
            // $className = str_replace('-', '_', $className);
            // $className = Inflector::id2camel($className);
            $className = explode('-', $className);
            $i = count($className)-1;
            unset($className[$i]);
            $i--;
            if(isset($className[$i])&&($className[$i] == '')) unset($className[$i]);
            $className = implode('-', $className);
            $className = Inflector::id2camel($className);
            $modelClass = $className;
        }
        if ($className == '') {
            // var_dump($originClassName);
            // $sam = str_replace('_', '-', $attribute);
            // $sam = Inflector::id2camel(substr($sam, $j));
            // var_dump($sam);
            // var_dump($type);
            echo '<pre>';
            var_dump($attribute);
            // exit();
            // var_dump($j);
            throw new \yii\base\Exception("getClassAttributeName: Le modèle de la classe $originClassName n'est pas encore généré");
            return;
        }
        $myClass = $path.'\models\\'.$modelClass;

        $model = new $myClass;
        $id_attribute = $attribute;
        while (!array_key_exists($id_attribute, $model->attributes)) {
            $id_attribute = explode('_', $id_attribute);
            $i = count($id_attribute)-1;
            unset($id_attribute[$i]);
            $id_attribute = implode('_', $id_attribute);
        }

        $id = $attribute;
        while ((!array_key_exists($id, $model->attributes))&&($id != '')) {
            $id = explode('_', $id);
            $i = count($id)-1;
            unset($id[$i]);
            $id = implode('_', $id);
        }
        if ($id == '') {
            $id = $id_attribute;
        }

        if ($className != 'User') {
            $nom_attribute = str_replace('id_', 'nom_', $attribute);
            $model = new $myClass;
            $state = substr($attribute, 0,5);
            if ($type == 'etat') $id_attribute = str_replace('etat_', 'id_', $attribute);
            else $id_attribute = $attribute;
            
            $columns = $myClass::getTableSchema()->columns;
            //Check if $attribute can be null
            // $allowNull = false;
            // foreach ($columns as $column) {
            //     if (($column->name == $attribute)&&($attribute == 'id_ma_table_test')) {
            //         var_dump($columns);
            //         exit();
            //         $allowNull = $column->allowNull;
            //     }
            // }
            //if $attribute can be null then populate $prefix
            // if ($allowNull) {
                $prefix = "(\$model->$attribute) ?";
                $suffix = " : NULL";
            // } else {
                // $prefix = NULL;
                // $suffix = NULL;
            // }
            $visible = null;

            extract($this->scanTable($columns));
            $nom_attribute = ($myNomPrenom)?'nomprenom':$myNom;
            if ($nom_attribute != 'nomprenom') {
                while (!array_key_exists($nom_attribute, $model->attributes)&&($nom_attribute != '')) {
                    $nom_attribute = explode('_', $nom_attribute);
                    $i = count($nom_attribute)-1;
                    unset($nom_attribute[$i]);
                    $nom_attribute = implode('_', $nom_attribute);
                }
                if ($nom_attribute == '') $nom_attribute = 'nom_'.str_replace('id_', '', $attribute);
            }
            $name = Inflector::camel2id($className);
            $route = "$name";
        } else {
            $nom_attribute = 'username';
            $route = '/user/admin/update';
            $prefix = "(\$model->id".$className.") ?";
            $suffix = " : '<span class=\"not-set\">Non lié à un compte</span>'";
            $visible = "\n\t\t\t\t'visible' => (\$model->idUser)?true:false,";
        }

        return [
            'className'=>$modelClass, 
            'id_attribute'=>($type!='etat')?$id_attribute:str_replace('etat_', 'id_', $id_attribute), 
            'attribute'=>str_replace('_', '', substr($id_attribute, $j)),
            'route' => $route,
            'urlRoute' => str_replace('-', '', $route),
            'nom_attribute' => $nom_attribute,
            'id' => $id,
            'path' => $path,
            'prefix' => $prefix,
            'suffix' => $suffix,
            'visible' => $visible,

        ];
    }

    protected function getDataFromIDColumn($attribute)
    {
        $className = explode('_', $attribute);
        unset($className[0]);
        $className = implode('_', $className);
        $attr = $className;
        $className = Inflector::id2camel($className);

        if ($className != 'User') $path = 'app'; else $path = 'common';

        $originClassName = $className;
        while (!class_exists($path . '\models\\' . $className) && ($className != '')) {
            // $className = explode('_', $className);
            $className = Inflector::camel2id($className);
            $className = str_replace('-', '_', $className);
            $className = Inflector::id2camel($className);
            $className = explode('_', $className);

            $i = count($className)-1;
            /* */
            $i--;
            if($className[$i] == '') unset($className[$i]);

            unset($className[$i]);
            $className = implode('_', $className);
        }
        if ($className == '') {
            echo ("Le modèle $originClassName n'est pas encore généré");
            exit();
        }
        $myClass = $path.'\models\\'.$className;
        


        // $model = new $myClass;
        // $state = substr($attribute, 0,5);
        // if ($state == 'etat_') $id_attribute = str_replace('etat_', 'id', $attribute);
        // else $id_attribute = $attribute;
        // while (!array_key_exists($id_attribute, $model->attributes)&&($id_attribute != '')) {
        //     $id_attribute = explode('_', $id_attribute);
        //     $i = count($id_attribute)-1;
        //     unset($id_attribute[$i]);
        //     $id_attribute = implode('_', $id_attribute);
        // }
        // if ($id_attribute == '') $id_attribute = 'id_'.$attr;
        // $attr = str_replace('id_', '', $id_attribute);



        if ($className != 'User') {
            $prefix = null;
            $suffix = null;
            $visible = null;
            $nom_attribute = str_replace('id_', 'nom_', $attribute);
            $myClass = $path.'\models\\'.$className;

            $model = new $myClass;
            $state = substr($attribute, 0,5);
            if ($state == 'etat_') $id_attribute = str_replace('etat_', 'id', $attribute);
            else $id_attribute = $attribute;
            
            $columns = $myClass::getTableSchema()->columns;
            extract($this->scanTable($columns));
            $nom_attribute = ($myNomPrenom)?'nomprenom':$myNom;
            if ($nom_attribute != 'nomprenom') {
                while (!array_key_exists($nom_attribute, $model->attributes)&&($nom_attribute != '')) {
                    $nom_attribute = explode('_', $nom_attribute);
                    $i = count($nom_attribute)-1;
                    unset($nom_attribute[$i]);
                    $nom_attribute = implode('_', $nom_attribute);
                }
                if ($nom_attribute == '') $nom_attribute = 'nom_'.str_replace('id_', '', $attribute);
            }
            $name = Inflector::camel2id($className);
            $route = "/$name/view";
        } else {
            $prefix = "(\$model->id".$className.") ?";
            $suffix = " : ''";
            $visible = "\n\t\t\t\t'visible' => (\$model->idUser)?true:false,";
            $nom_attribute = 'username';
            $route = '/user/admin/update';
        }
        return [
            'class' =>$className, 
            'id' => $id_attribute, 
            'attr' => $attr
        ];
    }
    /**
     * Generates code for active field
     * @param string $attribute
     * @return string
     */
    public function generateMyActiveField($generator, $attribute)
    {

        $tableSchema = $generator->getTableSchema();
        $class = $generator->modelClass;
        $pks = $class::primaryKey();
        $id = $pks[0];

        $etat = 'etat_'.substr(
            str_replace(
                'backend\models\\', 
                '', 
                \yii\helpers\Inflector::camel2id($class, '_')
            ),
            1
        );

        if ($tableSchema === false || !isset($tableSchema->columns[$attribute])) {
            if (preg_match('/^(password|pass|passwd|passcode)$/i', $attribute)) {
                return "\$form->field(\$model, '$attribute')->passwordInput()";
            } else {
                return "\$form->field(\$model, '$attribute')";
            }
        }
        $column = $tableSchema->columns[$attribute];
        if (strpos($column->name, 'datetime') !== false) {
            return "\$form->field(\$model, '$attribute')->widget(DateControl::classname(), [
        'type' => DateControl::FORMAT_DATETIME,
    ])";
        } else if ((strpos($column->name, 'date') !== false)&&($column->name!='update_time')&&($column->name!='updated_by')) {
	        return "\$form->field(\$model, '$attribute')->widget(DateControl::classname())";
	    } else if ((strpos($column->name, 'id_') !== false) && (!$column->autoIncrement)&&($column->name != $id)) {

            /** IN FUNCTION getClassAttributeName 
            $attribute = $column->name;
            $className = Inflector::id2camel(substr($attribute, 3));
            if ($className != 'User') $path = 'app'; else $path = 'common';

            $originClassName = $className;
            while ((!class_exists($path.'\models\\'.$className))&&(!class_exists('common\models\\'.$className))&&($className!='')) {
                $className = explode('_', $className);
                $i = count($className)-1;
                unset($className[$i]);
                $className = implode('_', $className);
            }
            if ($className == '') {
                echo ("Le modèle de la classe $originClassName n'est pas encore généré");
                exit();
            }
            $myClass = $path.'\models\\'.$className;
            
            $model = new $myClass;
            $id_attribute = $attribute;
            while (!array_key_exists($id_attribute, $model->attributes)) {
                $id_attribute = explode('_', $id_attribute);
                $i = count($id_attribute)-1;
                unset($id_attribute[$i]);
                $id_attribute = implode('_', $id_attribute);
            }
            **/
            extract($this->getClassAttributeName($column->name));

            // $columns = $myClass::getTableSchema()->columns;
            // $columns = \backend\models\Stade::getTableSchema();
            // extract($this->scanTable($columns));
            // $nom = ($myNomPrenom)?'nomprenom':$myNom;
            // if ($nom != 'nomprenom') {
            //     while (!array_key_exists($nom, $model->attributes)) {
            //         $nom = explode('_', $nom);
            //         $i = count($nom)-1;
            //         unset($nom[$i]);
            //         $nom = implode('_', $nom);
            //     }
            // }



            // $className = Inflector::id2camel(substr($attribute, 3));
            // $nom_attribute = str_replace('id_', 'nom_', $attribute);
            if ($nom_attribute == 'nomprenom') $addNom = ", true, 'nomprenom'";
            else if ($nom_attribute == 'username') $addNom = ", true, 'username'";
            else $addNom = NULL;
	        return "\$form->field(\$model, '$column->name')->widget(Select2::classname(), Yii::\$app->functions->getIDFieldOptions(\$model, '$id'$addNom))";
        } else if ($column->name == $etat) {
            $result = $this->getClassAttributeName($column->name);
            $route = $result['route'];
            return "(Yii::\$app->user->can('/$route/status')) ? \$form->field(\$model, '$column->name')->widget(SwitchInput::classname(), []) : ''";
	    } else if (strpos($column->name, 'etat_') !== false) {
            return "\$form->field(\$model, '$column->name')->widget(SwitchInput::classname(), []);";
	    } else if (strpos($column->name, 'photo_') !== false) {
	        return "\$form->field(\$model, '".$attribute."_field')->widget(FileInput::classname(), Yii::\$app->functions->getImageFieldOptions(\$model, '$attribute')) ?>
    <?= Html::activeHiddenInput(\$model, '$attribute')";
	    } else if (strpos($column->name, 'fichier_') !== false) {
	        return "\$form->field(\$model, '".$attribute."_field')->widget(FileInput::classname(), Yii::\$app->functions->getFileFieldOptions(\$model, '$attribute')) ?>
    <?= Html::activeHiddenInput(\$model, '$attribute')";
	    } else if (strpos($column->name, 'couleur_') !== false) {
	       return "(Yii::\$app->controller->action->id != 'ajax-create') ? \$form->field(\$model, '$attribute')->widget(ColorInput::classname(), Yii::\$app->functions->getColorFieldOptions()) : ''";
	    } else if (strpos($column->name, 'numero_') !== false) {
           return "\$form->field(\$model, '$attribute')->widget(TouchSpin::classname(), Yii::\$app->functions->getTouchspinFieldOptions())";
        }

        if ($column->phpType === 'boolean') {
            return "\$form->field(\$model, '$attribute')->checkbox()";
        } elseif ($column->type === 'text') {
            return "\$form->field(\$model, '$attribute')->textarea(['rows' => 6])";
        } else {
            if (preg_match('/^(password|pass|passwd|passcode)$/i', $column->name)) {
                $input = 'passwordInput';
            } else {
                $input = 'textInput';
            }
            if (is_array($column->enumValues) && count($column->enumValues) > 0) {
                $dropDownOptions = [];
                foreach ($column->enumValues as $enumValue) {
                    $dropDownOptions[$enumValue] = Inflector::humanize($enumValue);
                }
                return "\$form->field(\$model, '$attribute')->dropDownList("
                    . preg_replace("/\n\s*/", ' ', VarDumper::export($dropDownOptions)).", ['prompt' => ''])";
            } elseif ($column->phpType !== 'string' || $column->size === null) {
                return "\$form->field(\$model, '$attribute')->$input()";
            } else {
                return "\$form->field(\$model, '$attribute')->$input(['maxlength' => $column->size])";
            }
        }
    }

    public function generateMyActiveSearchField($generator, $attribute)
    {
        $tableSchema = $generator->getTableSchema();
        $class = $generator->modelClass;
        $pks = $class::primaryKey();
        $id = $pks[0];

        if ($tableSchema === false) {
            return "\$form->field(\$model, '$attribute')";
        }
        $column = $tableSchema->columns[$attribute];
        if ((strpos($column->name, 'date') !== false)&&($column->name!='update_time')&&($column->name!='updated_by')) {
            return "\$form->field(\$model, '$attribute')->widget(DateControl::classname(), ['options'=>['id'=>'search_".$attribute."', 'options' => ['placeholder' => \$model->getAttributeLabel('$attribute')]]])";
        } else if ((strpos($column->name, 'id_') !== false) && (!$column->autoIncrement)&&($column->name != $id)) {
       
            // $className = Inflector::id2camel(substr($attribute, 3));
            // if ($className != 'User') $path = 'app'; else $path = 'common';

            // $originClassName = $className;
            // while (!class_exists($path.'\models\\'.$className)&&($className != '')) {
            //     $className = explode('_', $className);
            //     $i = count($className)-1;
            //     unset($className[$i]);
            //     $className = implode('_', $className);
            // }
            // if ($className == '') {
            //     echo ("Le modèle de la classe $originClassName n'est pas encore généré");
            //     var_dump($attribute);
            //     exit();
            // }
            // $myClass = $path.'\models\\'.$className;

            // $model = new $myClass;
            // $columns = $myClass::getTableSchema()->columns;
            // // $columns = \backend\models\Stade::getTableSchema();
            // extract($this->scanTable($columns));
            // $nom = ($myNomPrenom)?'nomprenom':$myNom;
            // if ($nom != 'nomprenom') {
            //     while (!array_key_exists($nom, $model->attributes)&&($nom != '')) {
            //         $nom = explode('_', $nom);
            //         $i = count($nom)-1;
            //         unset($nom[$i]);
            //         $nom = implode('_', $nom);
            //     }
            //     if ($nom == '') $nom = 'nom_'.str_replace('id_', '', $attribute);
            // }

            // $id_attribute = $attribute;
            // while (!array_key_exists($id_attribute, $model->attributes)) {
            //     $id_attribute = explode('_', $id_attribute);
            //     $i = count($id_attribute)-1;
            //     unset($id_attribute[$i]);
            //     $id_attribute = implode('_', $id_attribute);
            // }
            extract($this->getClassAttributeName($column->name));
            // return array(
            //     'className'=>$modelClass, 
            //     'id_attribute'=>($type!='etat')?$id_attribute:str_replace('etat_', 'id_', $id_attribute), 
            //     'attribute'=>str_replace('_', '', substr($id_attribute, $j)),
            //     'route' => $route,
            //     'nom_attribute' => $nom_attribute,
            //     'id' => $id,
            // );

            return "\$form->field(\$model, '$column->name')->widget(Select2::classname(), [
        'data' => ArrayHelper::map($path\models\\$className::find()->all(), '$id', '$nom_attribute'),
        'options' => ['placeholder' => '--'.\$model->getAttributeLabel('$column->name').'--', 'id' => 'search_".str_replace('-', '_', Inflector::camel2id($className))."'],
        'pluginOptions' => ['allowClear' => true],
    ])";
        } else if (strpos($column->name, 'etat_') !== false) {
            return "\$form->field(\$model, '$attribute')->widget(Select2::classname(), [
        'data' => \common\models\Lookup::items('Etat'),
        'options' => ['placeholder' => \$model->getAttributeLabel('$attribute')],
        'pluginOptions' => ['allowClear' => true],
    ])";
        } else if (strpos($column->name, 'couleur_') !== false) {
           return "\$form->field(\$model, '$attribute')->widget(ColorInput::classname(), array_merge(Yii::\$app->functions->getColorFieldOptions(), ['options'=>['id'=>'$attribute', 'placeholder'=>\$model->getAttributeLabel('$attribute')]]))";
        } else if (strpos($column->name, 'numero_') !== false) {
           return "\$form->field(\$model, '$attribute')->widget(TouchSpin::classname(), Yii::\$app->functions->getTouchspinFieldOptions(\$model, '$attribute', true))";
        } else if (($column->name == 'created_by')||($column->name == 'updated_by')) {
            return "\$form->field(\$model, '$attribute')->widget(Select2::classname(), [
        'data' => ArrayHelper::map(common\models\User::find()->all(), 'id', 'username'),
        'options' => ['placeholder' => \$model->getAttributeLabel('$attribute'), 'id' => 'search_".$attribute."'],
        'pluginOptions' => ['allowClear' => true],
    ])";
        }
        if ($column->phpType === 'boolean') {
            return "\$form->field(\$model, '$attribute')->checkbox()";
        } else {
            return "\$form->field(\$model, '$attribute')";
        }
    }

    public function generateMyColumns($tableSchema, $attribute, $id, $comment)
    {
        $format = $this->checkFormat($attribute, $id);
        $slashs = ($comment)?'// ':'';
        if ($id) {
            return "[
                'attribute' => '$attribute',
                'contentOptions'=>['style'=>'width: 0.5%'],
            ],";
        } else if ($format == 'date') {
            return "$slashs [
            $slashs    'attribute' => '$attribute',
            $slashs    'format' => 'date',
            $slashs    'filterType'=>GridView::FILTER_DATE,
            $slashs    'filterWidgetOptions'=>['pluginOptions' => [
            $slashs        'autoclose'=>true,
            $slashs        'format' => 'yyyy-mm-dd'
            $slashs    ]],
            ".$slashs."],";
        } else if ($format == 'id') {
            // $className = str_replace('_', '-', $attribute);
            // $className = Inflector::id2camel(substr($className, 3));
            // $className = Inflector::camel2words(substr($className, 3));
            // $placeholder = str_replace('_', ' ', $className);

            // if ($className != 'User') $path = 'app'; else $path = 'common';
            // // if ($className == 'ClubPrecedent') {
            // //     $className = Inflector::camel2id($className);
            // //     $className = str_replace('-', '_', $className);
            // //     $className = Inflector::id2camel($className);
            // //     $className = explode('_', $className);
            // //     $i = count($className)-1;
            // //     unset($className[$i]);
            // //     $i--;
            // //     if($className[$i] == '') unset($className[$i]);
            // //     $className = implode('_', $className);
            // // }
            // while (!class_exists($path.'\models\\'.$className)) {
            //     $className = Inflector::camel2id($className);
            //     $className = str_replace('-', '_', $className);
            //     $className = Inflector::id2camel($className);
            //     $className = explode('_', $className);
            //     $i = count($className)-1;
            //     unset($className[$i]);
            //     $i--;
            //     if($className[$i] == '') unset($className[$i]);
            //     $className = implode('_', $className);
            // }
            // $myClass = $path.'\models\\'.$className;
            // $model = new $myClass;

            // $columns = $myClass::getTableSchema()->columns;
            // // $columns = \backend\models\Stade::getTableSchema();
            // extract($this->scanTable($columns));
            // $nom = ($myNomPrenom)?'nomprenom':$myNom;
            // if ($nom != 'nomprenom') {
            //     while (!array_key_exists($nom, $model->attributes)&&($nom != '')) {
            //         $nom = explode('_', $nom);
            //         $i = count($nom)-1;
            //         unset($nom[$i]);
            //         $nom = implode('_', $nom);
            //     }
            //     if ($nom == '') $nom = 'nom_' . Inflector::camel2id($className);
            // }

            // $id_attribute = $attribute;
            // while (!array_key_exists($id_attribute, $model->attributes)) {
            //     $id_attribute = explode('_', $id_attribute);
            //     $i = count($id_attribute)-1;
            //     unset($id_attribute[$i]);
            //     $id_attribute = implode('_', $id_attribute);
            // }
            // // $nom_attribute = str_replace('id_', 'nom_', $attribute);
            // 'className'=>$modelClass, 
            // 'id_attribute'=>($type!='etat')?$id_attribute:str_replace('etat_', 'id_', $id_attribute), 
            // 'attribute'=>str_replace('_', '', substr($id_attribute, $j)),
            // 'route' => $route,
            // 'nom_attribute' => $nom_attribute,
            // 'id' => $id,
            // 'path' => $path,
            // 'prefix' => $prefix,
            // 'suffix' => $suffix,
            // 'visible' => $visible,

            $column = $tableSchema->columns[$attribute];
            extract($this->getClassAttributeName($column->name));
            if ($nom_attribute == 'nomprenom') $attname = ", 'nomprenom'"; else $attname = NULL;
            return $slashs."[
            $slashs    'format' => ['link', '$className'$attname],
            $slashs    'attribute' => '$column->name',
            $slashs    'filterType'=>GridView::FILTER_SELECT2,
            $slashs    'filterWidgetOptions' => [
            $slashs        'data' => \yii\helpers\ArrayHelper::map($path\models\\$className::find()->all(), '$id', '$nom_attribute'),
            $slashs        'options' => ['placeholder' => '-".Inflector::camel2words($className)."-'],
            $slashs        'pluginOptions' => ['allowClear' => true],
            $slashs    ],
            ".$slashs."],";
        } else if ($format == 'etat') {
            return $slashs."[
            $slashs    'class' => 'kartik\grid\BooleanColumn',
            $slashs    'attribute' => '$attribute', 
            $slashs    'vAlign' => 'middle',
            $slashs    'width' => '3%',
            $slashs    'trueIcon' => '<span class=\"cercle vert\"></span>',
            $slashs    'falseIcon' => '<span class=\"cercle rouge\"></span>',
            ".$slashs."],";
        } else if ($format == 'create_time') {
            return $slashs."[
            $slashs    'attribute' => '$attribute',
            $slashs    'format' => 'datetime',
            $slashs    'width'=>'13%',
            $slashs    'filterType'=>GridView::FILTER_DATE,
            $slashs    'filterWidgetOptions'=>[
            $slashs        'pluginOptions' => [
            $slashs            'autoclose'=>true,
            $slashs            'format' => 'dd/mm/yyyy',
            $slashs    ]],
            ".$slashs."],";
        } else if ($format == 'created_by') {
            return "$slashs'$attribute:user',";
        } else if ($format == 'photo') {
            return $slashs."[
            $slashs    'format' => ['photo', '".substr($attribute, 6)."', ['width'=>'48']],
            $slashs    'attribute' => '$attribute',
            ".$slashs."],";
        } else if ($format == 'file') {
            return $slashs."[
            $slashs    'attribute' => '$attribute',
            $slashs    'format' => 'file',
            $slashs    'width' => '1%',
            $slashs    'label' => 'F.',
            $slashs    'vAlign'=>'middle',
            ".$slashs."],";
        } else if ($format == 'color') {
            return $slashs."[
            $slashs    'format' => ['color', true],
            $slashs    'attribute' => '$attribute',
            $slashs    'width' => '3%',
            $slashs    'filterType' => GridView::FILTER_COLOR,
            $slashs    'filterWidgetOptions' => Yii::\$app->functions->getColorFieldOptions(),
            $slashs    'vAlign' => 'middle',
            ".$slashs."],";
        } else if ($format == 'numero') {
            return $slashs."[
            $slashs    'attribute' => '$attribute',
            $slashs    'filterType' => GridView::FILTER_SPIN,
            $slashs    'filterWidgetOptions' => Yii::\$app->functions->getTouchspinFieldOptions(),
            ".$slashs."],";
        } else if ($format == 'email') {
            return "$slashs'$attribute:email',";
        } else {
            return "$slashs'$attribute',";
        }
    }

    public function generateMyViewColumns($column)
    {
        $attribute = $column->name;
        $id = $column->autoIncrement;
        $format = $this->checkFormat($attribute, $id);


        if ($column->isPrimaryKey) return "'$attribute'";
        if ($format == 'date') {
            return "'$attribute:$column->type'";
        } else if (strpos($attribute, 'etat_') !== false) {
            return "'$attribute:badgeBoolean'";
        } else if ($format == 'id') {

            // extract($attribute);
            extract($this->getClassAttributeName($column->name));
            /* HERE */
            // $className = Inflector::id2camel(substr($attribute, 3));
            // $name = Inflector::camel2id(substr($attribute, 3));
            // if ($className != 'User') $path = 'app'; else $path = 'common';

            // $originClassName = $className;
            // while (!class_exists($path.'\models\\'.$className)&&($className != '')) {
            //     $className = Inflector::camel2id($className);
            //     $className = str_replace('-', '_', $className);
            //     $className = Inflector::id2camel($className);
            //     $className = explode('_', $className);
            //     $i = count($className)-1;
            //     unset($className[$i]);
            //     $i--;
            //     if($className[$i] == '') unset($className[$i]);
            //     $className = implode('_', $className);
            // }
            // if ($className == '') {
            //     echo "Le model $originClassName n'existe pas";
            //     exit();
            // }
            

            // if ($className != 'User') {
            //     $prefix = null;
            //     $suffix = null;
            //     $visible = null;
            //     $nom_attribute = str_replace('id_', 'nom_', $attribute);
            //     $myClass = $path.'\models\\'.$className;

            //     $model = new $myClass;
            //     $columns = $myClass::getTableSchema()->columns;
            //     // $columns = \backend\models\Stade::getTableSchema();
            //     extract($this->scanTable($columns));
            //     $nom_attribute = ($myNomPrenom)?'nomprenom':$myNom;
            //     if ($nom_attribute != 'nomprenom') {
            //         while (!array_key_exists($nom_attribute, $model->attributes)&&($nom_attribute != '')) {
            //             $nom_attribute = explode('_', $nom_attribute);
            //             $i = count($nom_attribute)-1;
            //             unset($nom_attribute[$i]);
            //             $nom_attribute = implode('_', $nom_attribute);
            //         }
            //         if ($nom_attribute == '') $nom_attribute = 'nom_'.str_replace('id_', '', $attribute);
            //     }
            //     $name = Inflector::camel2id($className);
            //     $route = "/$name/view";
            // } else {
            //     $prefix = "(\$model->id".$className.") ?";
            //     $suffix = " : ''";
            //     $visible = "\n\t\t\t\t'visible' => (\$model->idUser)?true:false,";
            //     $nom_attribute = 'username';
            //     $route = '/user/admin/update';
            // }
            if ($route == '/user/admin/update') {
                $urlRoute = '/user/admin/update';
            } else { 
                $urlRoute = '/'.$urlRoute.'/view';
                $route = '/'.$route.'/view';
            }
            return "[
                'attribute'=>'$column->name',
                'value' => $prefix ((Yii::\$app->user->can('$route')) ? Html::a(\$model->id".$className."->$nom_attribute,['$urlRoute', 'id'=>\$model->$column->name], ['target'=>'_blank']) : \$model->id".$className."->$nom_attribute)$suffix,
                'format'=>'raw',".$visible."
            ]";
        } else if ($format == 'create_time') {
            return "[
                'attribute' => 'create_time',
                'format' => 'raw',
                'value' => Yii::\$app->functions->created(\$model),
            ],
            [
                'attribute' => 'update_time',
                'format' => 'raw',
                'value' => Yii::\$app->functions->updated(\$model),
            ]";
        } else if ($format == 'photo') {
            return "[
                'attribute'=>'$attribute',
                'value' => Yii::\$app->functions->getModalContent(\$model, '$attribute'),
                'format' => 'raw', 
                'visible' => (\$model->$attribute),
            ]";
        } else if ($format == 'file') {
            return "'$attribute:file'";
        } else if ($format == 'color') {
            return "'$attribute:color'";
        } else if ($format == 'email') {
            return "'$attribute:email'";
        } else {
            return "'$attribute'";
        }
    }

    /**
     * Generates validation rules for the search model.
     * @return array the generated validation rules
     */
    public function generateMySearchRules($generator)
    {
        if (($table = $generator->getTableSchema()) === false) {
            return ["[['" . implode("', '", $generator->getColumnNames()) . "'], 'safe']"];
        }
        $types = [];
        foreach ($table->columns as $column) {
            switch ($column->type) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                    if (($column->name != 'create_time')&&($column->name != 'update_time'))
                        $types['integer'][] = $column->name;
                    if (($column->name == 'create_time')||($column->name == 'update_time')) 
                        $types['safe'][] = $column->name;
                    break;
                case Schema::TYPE_BOOLEAN:
                    $types['boolean'][] = $column->name;
                    break;
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                    $types['number'][] = $column->name;
                    break;
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                default:
                    $types['safe'][] = $column->name;
                    break;
            }
        }

        $rules = [];
        foreach ($types as $type => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], '$type']";
        }

        return $rules;
    }

    /**
     * Generates search conditions
     * @return array
     */
    public function generateMySearchConditions($generator)
    {
        $columns = [];
        if (($table = $generator->getTableSchema()) === false) {
            $class = $generator->modelClass;
            /* @var $model \yii\base\Model */
            $model = new $class();
            foreach ($model->attributes() as $attribute) {
                $columns[$attribute] = 'unknown';
            }
        } else {
            foreach ($table->columns as $column) {
                $columns[$column->name] = $column->type;
            }
        }
        $create_update = false;
        $likeConditions = [];
        $hashConditions = [];
        foreach ($columns as $column => $type) {
            switch ($type) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                case Schema::TYPE_BOOLEAN:
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                    if (($column != 'create_time')&&($column != 'update_time'))
                        $hashConditions[] = "'{$column}' => \$this->{$column},";
                    else $create_update = true;
                    break;
                default:
                    $likeConditions[] = "->andFilterWhere(['like', '{$column}', \$this->{$column}])";
                    break;
            }
        }

        $conditions = [];
        if (!empty($hashConditions)) {
            $conditions[] = "\$query->andFilterWhere([\n"
                . str_repeat(' ', 12) . implode("\n" . str_repeat(' ', 12), $hashConditions)
                . "\n" . str_repeat(' ', 8) . "]);\n";
        }
        if ($create_update) {
            $conditions[] = "if (\$this->create_time) {
            \$query->andFilterWhere([
                'between', 
                'create_time', 
                Yii::\$app->functions->dateToTimestamp(\$this->create_time), 
                Yii::\$app->functions->dateToTimestamp(\$this->create_time.' 23:59:59')
            ]);
        }
        if (\$this->update_time) {
            \$query->andFilterWhere([
                'between', 
                'update_time', 
                strtotime(\$this->update_time.' 00:00:00'), 
                strtotime(\$this->update_time.' 23:59:59')
            ]);
        }\n";
        }
        if (!empty($likeConditions)) {
            $conditions[] = "\$query" . implode("\n" . str_repeat(' ', 12), $likeConditions) . ";\n";
        }

        return $conditions;
    }

    public function getMyNameAttribute($columns)
    {
        extract($this->scanTable($columns));
        $nom = ($myNomPrenom)?'nomprenom':$myNom;
        if ($nom == 'nomprenom') return $nom;


        foreach ($columns as $column) {
            if (!strcasecmp($column->name, 'name') || !strcasecmp($column->name, 'title')) {
                return $name;
            }
            if (strpos($column->name, 'nom_') !== false) {
                return $column->name;
            }
            if ($column->isPrimaryKey) $pk = $column->name;
        }
        return str_replace('id_', 'nom_', $pk);
        // var_dump($columns);
        // exit();
        /* @var $class \yii\db\ActiveRecord */
        // $class = $this->modelClass;
        // $pk = $column::primaryKey();
        // $pk = $column->autoIncrement;

        // return $pk;
    }
}