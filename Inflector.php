<?php

/**
 * @copyright Copyright &copy; Samir IZZA, 2017
 * @package yii2-helpers
 * @version 1.0.0
 */

namespace vendor\samirmember\helpers;

use Yii;
use yii\helpers\Inflector as BaseInflector;

/**
 * Inflector override the Yii Inflector class to add some personal functions.
 *
 * @author Samir IZZA <samirmember@gmail.com>
 * @since 2.0
 */
class Inflector extends BaseInflector
{
    /**
     * Converts a word to its plural form.
     * Note that this is for French only!
     * For example, 'Jeu' will become 'Jeux', and 'enfant' will become 'enfants'.
     * @param string $word the word to be pluralized
     * @return string the pluralized word
     */
    public static $specials = [
        'Pays' => 'Pays'
    ];

    public static $accent = [
        'Societe'   => 'Société',
        'societe'   => 'société',
        'Tache'     => 'Tâche',
        'tache'     => 'tâche',
    ];

    public static $plurals = [
        '/([nrlm]ese|deer|fish|sheep|measles|ois|pox|media)$/i' => '\1',
        '/^(sea[- ]bass)$/i' => '\1',
        '/(m)ove$/i' => '\1oves',
        '/(f)oot$/i' => '\1eet',
        '/(h)uman$/i' => '\1umans',
        '/(s)tatus$/i' => '\1tatuses',
        '/(s)taff$/i' => '\1taff',
        '/(t)ooth$/i' => '\1eeth',
        '/(quiz)$/i' => '\1zes',
        '/^(ox)$/i' => '\1\2en',
        '/([m|l])ouse$/i' => '\1ice',
        '/(matr|vert|ind)(ix|ex)$/i' => '\1ices',
        '/(x|ch|ss|sh)$/i' => '\1es',
        '/([^aeiouy]|qu)y$/i' => '\1ies',
        '/(hive)$/i' => '\1s',
        '/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
        '/sis$/i' => 'ses',
        '/([ti])um$/i' => '\1a',
        '/(p)erson$/i' => '\1eople',
        '/(m)an$/i' => '\1en',
        '/(c)hild$/i' => '\1hildren',
        '/(buffal|tomat|potat|ech|her|vet)o$/i' => '\1oes',
        '/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|vir)us$/i' => '\1i',
        '/us$/i' => 'uses',
        '/(alias)$/i' => '\1es',
        '/(ax|cris|test)is$/i' => '\1es',
        '/s$/' => 's',
        '/^$/' => '',
        '/$/' => 's',
    ];

    public static function pluralize($word)
    {
        if (isset(static::$specials[$word])) {
            return static::$specials[$word];
        }
        foreach (static::$plurals as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                return preg_replace($rule, $replacement, $word);
            }
        }

        return $word;
    }

    public static function listTitle()
    {
        // $words = str_replace('-',' ', strtolower(Yii::$app->controller->id));
        return 'Liste des '.
            static::pluralize(
                static::titleize(
                    str_replace('-',' ', 
                        strtolower(Yii::$app->controller->id)
                    )
                )
            )
        ;
    }


    /**
     * Converts an non or CamelCase word into a French
     * sentence.
     * @param string $words
     * @param boolean $ucAll whether to set all words to uppercase
     * @return string
     */
    public static function titleize($words, $ucAll = false)
    {
        if (isset(static::$accent[$words])) $words = static::$accent[$words];
        // return $words;
        // var_dump($words);
        // exit();
        // $words = static::humanize($words, $ucAll);
        return $ucAll ? ucwords($words) : ucfirst($words);
    }
}