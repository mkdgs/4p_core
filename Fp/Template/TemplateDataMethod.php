<?php

namespace Fp\Template;

use Fp\Core;
use Fp\Core\Filter;
use Netcarver\Textile\Parser;

/**
 * Copyright Desgranges Mickael
 * mickael@4publish.com
 *
 * Ce logiciel est un programme informatique servant à la création d'application web.
 *
 * Ce logiciel est régi par la licence CeCILL-B soumise au droit français e
 * respectant les principes de diffusion des logiciels libres. Vous pouvez
 * utiliser, modifier et/ou redistribuer ce programme sous les conditions
 * de la licence CeCILL-B telle que diffusée par le CEA, le CNRS et l'INRIA
 * sur le site "http://www.cecill.info".
 *
 * En contrepartie de l'accessibilité au code source et des droits de copie,
 * de modification et de redistribution accordés par cette licence, il n'est
 * offert aux utilisateurs qu'une garantie limitée.  Pour les mêmes raisons,
 * seule une responsabilité restreinte pèse sur l'auteur du programme,
 * titulaire des droits patrimoniaux et les concédants successifs.
 *
 * A cet égard  l'attention de l'utilisateur est attirée sur les risques
 * associés au chargement,  à l'utilisation,  à la modification et/ou au
 * développement et à la reproduction du logiciel par l'utilisateur étant
 * donné sa spécificité de logiciel libre, qui peut le rendre complexe à
 * manipuler et qui le réserve donc à des développeurs et des professionnels
 * avertis possédant  des  connaissances  informatiques approfondies.  Les
 * utilisateurs sont donc invités à charger  et  tester  l'adéquation  d
 * logiciel à leurs besoins dans des conditions permettant d'assurer la
 * sécurité de leurs systèmes et ou de leurs données et, plus généralement,
 * à l'utiliser et l'exploiter dans les mêmes conditions de sécurité.
 *
 * Le fait que vous puissiez accéder à cet en-tête signifie que vous avez
 * pris connaissance de la licence CeCILL-B, et que vous en avez accepté les
 * termes.
 *
 * @package		4_publish
 * @subpackage	core
 * @author		Desgranges Mickael
 * @license		CeciLL-B
 * @link			http://4publish.com
 * @property TemplateData
 */
class TemplateDataMethod {

    public static function filter($t, $args = null) {
        if (!is_array($args))
            $args = func_get_args();
        array_shift($args);
        $r = new TemplateData();
        foreach ($args as $v) {
            if (array_key_exists($v, $t->vars)) {
                $r[$v] = $t->vars[$v];
            }
        }
        return $r;
    }

    public static function filterNot($t, $args = null) {
        if (!is_array($args))
            $args = func_get_args();
        array_shift($args);
        $r = new TemplateData();
        while ($v = $t->iterate()) {
            if (!in_array((string) $v->key, $args)) {
                $r[$v->key] = $v;
            }
        }
        return $r;
    }

    public static function getIf($t, $args = null) {
        if (!is_array($args)) {
            $args = func_get_args();
            array_shift($args);
        }
        $o = &$t;
        foreach ($args as $v) {
            if (is_array($o->vars) AND array_key_exists($v, $o->vars))
                $o = &$o->vars[$v];
            else
                return null;
        }
        return $o;
    }

    public static function is($t, $args = null) {
        if (!is_array($args))
            $args = func_get_args();
        array_shift($args);
        $o = &$t;
        foreach ($args as $v) {
            if (is_array($o->vars) AND array_key_exists($v, $o->vars) AND $o->vars[$v] instanceof TemplateData)
                $o = &$o->vars[$v];
            else
                return new TemplateData(null); // fix: set null because empty string return 1 on count() 
        }
        return $o;
    }

    public static function orElse($t, $args = null) {
        if (!is_array($args))
            $args = func_get_args();
        array_shift($args);
        $o = &$t;
        // si data->orElse('is false')
        // return un objet templateData 'is false' si  data->v() est faux
        // sinon data est retourné
        if (count($args) === 1) {
            if ($o->v()) {
                return $o;
            } else {
                return new TemplateData($args[0]);
            }
        }
        // si data->orElse('boolean true or false', 'is false')
        // return un objet templateData 'is false' si  le premier argument est faux
        // sinon data est retourné
        else {
            if ($args[0]) {
                return $o;
            } else {
                return new TemplateData($args[1]);
            }
        }
        return new TemplateData(null);
    }

    public static function eq($t, $index = 0) {
        $o = &$t;
        if (is_array($o->vars)) {
            $array = array_slice($o->vars, $index, 1, $preserve_keys = true);
            if (!empty($array)) {
                return current($array);
            }
        }
        return new TemplateData(null);
    }

    public static function exists($t, $args = null) {
        if (!is_array($args))
            $args = func_get_args();
        array_shift($args);
        $o = &$t;
        foreach ($args as $v) {
            if (is_array($o->vars) AND array_key_exists($v, $o->vars))
                $o = &$o->vars[$v];
            else
                return false;
        }
        return true;
    }

    /** @deprecated since 4.0  */
    public static function issetIf($t, $args = null) {
        return self::exists($t, $args);
    }

    public static function valueIf($t) {
        $args = func_get_args();
        $v = call_user_func_array(array(__NAMESPACE__ . '\TemplateDataMethod', 'getIf'), $args);
        return ( $v instanceof TemplateData ) ? $v->v() : $v;
    }

    public static function echoIf($t) {
        $args = func_get_args();
        echo call_user_func_array(array(__NAMESPACE__ . '\TemplateDataMethod', 'getIf'), $args);
    }

    public static function get($t, $name, $default = null) {
        return ( $t->__isset($name) ) ? $t->vars[$name]->vars : $default;
    }

    public static function getEcho($t, $name, $default = null) {
        echo ( $t->__isset($name) ) ? $t->vars[$name]->vars : $default;
    }

    public static function toArray($t) {
        if (!is_array($t->vars))
            return array(self::v($t));
        $a = array();
        foreach ($t->vars as $k => $i) {
            if ($i instanceof TemplateData) {
                $a[$k] = ( $i instanceof TemplateData AND ! is_scalar($i->vars) && $i->vars !== null ) ? self::toArray($i) : $i->vars;
            } else {
                $a[$k] = (!is_scalar($i) && $i !== null ) ? self::toArray($i) : $i;
            }
        }
        return $a;
    }

    public static function toJson($t) {
        $v = $t->v();
        if (is_scalar($v) || $v === null)
            return json_encode($v);
        return json_encode($t->toArray());
    }

    /**
     * @deprecated
     */
    public static function json_encode($t) {
        return json_encode($t->toArray());
    }

    public static function e($t) {
        echo self::value($t);
    }

    public static function es($t) {
        echo stripslashes(self::value($t));
    }

    public static function count($t) {
        return count($t->vars);
    }

    public static function name($t) {
        return $t->key;
    }

    public static function data($t) {
        return $t->vars;
    }

    public static function value($t) {
        if ($t->vars === null)
            return '';
        return ( is_scalar($t->vars) ) ? strval($t->vars) : $t->vars;
    }

    public static function v($t) {
        return self::value($t);
    }

    public static function replace($t) {
        return self::r($t);
    }

    public static function url($t, \Fp\Core\Init $O, $keyword = null) {
        $c = clone $t;
        $c->vars = $O->route()->rewriteUrl($c->vars, $keyword);
        return $c;
    }

    /**
     * @param unknown $t
     * @param unknown $url
     * @return unknown
     * @deprecated
     */
    public static function urlRelativ($t, \Fp\Core\Init $O, $url) {
        $c = clone $t;
        $r = $o->glob('url') . '/' . $O->route()->getRoute();
        $c->vars = preg_replace("#$r#", '.', $c->vars);
        return $c;
    }

    /**
     * @param unknown $t
     * @param string $url
     * @param array $params
     * @return unknown
     * @deprecated
     */
    public static function urlAddParams($t, $params) {
        $c = clone $t;

        $oldParams = array();
        $url = parse_url($t->vars);

        parse_str($url['query'], $oldParams);
        $params = array_merge($oldParams, $params);
        $url['query'] = http_build_query($params);

        $scheme = isset($url['scheme']) ? $url['scheme'] . '://' : '';
        $host = isset($url['host']) ? $url['host'] : '';
        if ($host && !$scheme)
            $scheme = '//';
        $port = isset($url['port']) ? ':' . $url['port'] : '';
        $user = isset($url['user']) ? $url['user'] : '';
        $pass = isset($url['pass']) ? ':' . $url['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($url['path']) ? $url['path'] : '';
        $query = isset($url['query']) ? '?' . $url['query'] : '';
        $fragment = isset($url['fragment']) ? '#' . $url['fragment'] : '';

        $c->vars = "$scheme$user$pass$host$port$path$query$fragment";
        return $c;
    }

    public static function r($t) {
        $args = func_get_args();
        array_shift($args);
        $c = clone $t;
        foreach ($args as $d) {
            if ($d instanceof TemplateData)
                $c->vars = preg_replace(array("#\{$d->key\}|%7B{$d->key}%7D#"), $d->v(), $c->vars);
            elseif (is_array($d)) {
                foreach ($d as $k => $v)
                    $c->vars = preg_replace(array("#\{$k\}|%7B{$k}%7D#"), $v, $c->vars);
            }
        }
        return $c;
    }

    public static function int($t) {
        return (int) $t->__toString();
    }

    public static function groupBy($t, $group_key, $group_function = null) {
        if (!$t instanceof TemplateData)
            return null;
        if (!is_array($t->vars))
            return null;
        $groups = array();

        if (is_callable($group_key)) {
            foreach ($t->vars as &$item) {
                if ($item instanceof TemplateData) {
                    $key = $group_key($item);
                    if (!$key)
                        continue;
                    if (!array_key_exists($key, $groups)) {
                        $groups[$key] = array();
                    }
                    $groups[$key][] = $item->vars;
                }
            }
        } else if ($group_function) { // déprécié	                    
            while ($item = $t->iterate()) {
                if (!$item->exists($group_key))
                    continue;
                $key = $group_function($item[$group_key]);
                if (!array_key_exists($key, $groups)) {
                    $groups[$key] = array();
                }
                $groups[$key][] = $item->vars;
            }
        } else {
            foreach ( $t->vars as $k => &$item) {
                if ($item instanceof TemplateData) {
                    if (!$item->exists($group_key))
                        continue;
                    $key = $item[$group_key];

                    if (!array_key_exists($key, $groups)) {
                        $groups[$key] = array();
                    }
                    $groups[$key][] = $item->vars;
                }
            }
        }
        return new TemplateData($groups, 'groupBy');
    }

    public static function iteratePosition($t) {
        if ($t->i_position === null)
            return $t->i_position = $t->i_total - $t->i_iterate;
        return $t->i_position;
    }

    public static function iterateIsLast($t) {
        return 1 == $t->i_iterate;
    }

    public static function iterate($t, $nb = null, $offset = null, $rewind = false) {
        if (!$t instanceof TemplateData)
            return null;
        if (!is_array($t->vars))
            return null;
        
        if ($t->i_iterate == null) {
            if ($rewind) {
                $t->end();
            } else {
                $t->reset();
            }
            if ($offset) {
                if ($offset > count($t)) {
                    return null;
                } else {
                    for ($i = 0; $i != $offset; $i++) {
                        if ($rewind)
                            $t->prev();
                        else
                            $t->next();
                    }
                }
            }
            if ($nb === null) {
                $t->i_total = count($t);
                $t->i_iterate = $t->i_total + 1;
            } else {
                $t->i_iterate = $nb + 1;
                $t->i_total = $nb;
            }
        }
        $t->i_iterate--;
        $t->i_position = null;

        if ($t->i_iterate > 0 && $a = $t->current()) {
            if (!$rewind)
                $t->next();
            else
                $t->prev();
            $a->i_position = self::iteratePosition($t);
            return $a;
        }
        $t->i_total = $t->i_iterate = null;
        reset($t->vars);
    }

    public static $extra_methods = array();

    public static function __callStatic($name, $args) {
        if (array_key_exists($name, self::$extra_methods)) {
            $fx = self::$extra_methods[$name];
            return call_user_func_array($fx, $args);
        }
    }

    public static function setMethod($name, $callable) {
        self::$extra_methods[$name] = $callable;
    }

    /*
     *  TRANSFORMATION
     */

    public static function jsString($t) {
        $c = clone $t;
        $v = $c->v();
        if (is_scalar($v) || $v === null)
            $c->vars = json_encode($v);
        else
            $c->vars = json_encode($c->toAarray());
        return $c;
    }

    public static function unescape($t) {
        $c = clone $t;
        $c->vars = stripslashes($c->vars);
        return $c;
    }

    public static function escape($t, $charlist = '') {
        $c = clone $t;
        if ($charlist) {
            $c->vars = addcslashes($c->vars, $charlist);
        } else
            $c->vars = addslashes($c->vars);
        return $c;
    }

    /**
     * protège une chaine destiné a être affichée dans une attribut html (ex:title)
     * 
     * @param unknown $t
     * @return unknown
     */
    public static function escapeAttribute($t) {
        $c = clone $t;
        $c->vars = htmlspecialchars($c->vars, ENT_QUOTES, 'UTF-8', true);
        return $c;
    }

    public static $classTextile;

    public static function textile($t, $restricted = true) {
        $c = clone $t;
        if (!isset(self::$classTextile)) {
            //require __DIR__.'/../../lib/Netcarver/Textile/Parser.php';
            //require __DIR__.'/../../lib/Netcarver/Textile/DataBag.php';
            //require __DIR__.'/../../lib/Netcarver/Textile/Tag.php';
            self::$classTextile = new \Netcarver\Textile\Parser('html5');
            self::$classTextile->setRelativeImagePrefix('./mod/FpModule%5CMedia%5CModule/html/image/');
        }
        // self::$classTextile->setRestricted($restricted);
        // gestion des image associés aux node
        // à corriger le contenu html est déja encodé, si on le décode ce code html sera interprété 
        // Filter::decodeHtmlChars(
        $c->vars = self::$classTextile->TextileThis($c->vars);

        $r = '@<p><br />[\s]*((!image|!video)([^<]*))<br />[\s]*</p>@u';
        $c->vars = preg_replace($r, '$1', $c->vars);


        $rgxAttr = '(?:{(?P<css>((width|height|margin|padding)+:[0-9]+(px|%);)*)})?';
        $rgxCredit = '(?:\[(?P<credit>[^\]]*)\])?';
        $rgxUrl = '(?P<url>([0-9]+|[/?\pL0-9-_\.+&%:#=;,]+))';
        $regex = array(
            "@(!image$rgxAttr:$rgxUrl$rgxCredit)@u",
            "@!video$rgxAttr:$rgxUrl$rgxCredit@u",
            "@!music$rgxAttr:$rgxUrl$rgxCredit@u",
            "@!link$rgxAttr:$rgxUrl$rgxCredit@u"
        );

        $replace = array(
            '<div data-linked-media="(?P=url)" data-embed-image="(?P=url)" style="(?P=css)" data-credit="(?P=credit)"></div>',
            '<div data-embed-video="(?P=url)" data-credit="(?P=credit)" ></div>',
            '<div data-embed-music="(?P=url)" style="(?P=css)" data-credit="(?P=credit)"></div>',
            '<a data-embed-link="(?P=url)" href="(?P=url)" style="(?P=css)" data-credit="(?P=credit)">(?P=url)</a>'
        );
        //$c->vars = preg_replace($regex, $replace, $c->vars);

        $myReplace = function ($regex, $replace, $subject) {
            $extendReplace = function ($matches) use ($replace) {
                $string = array_shift($matches);

                foreach ($matches as $k => $v) {
                    if (!ctype_digit("$k")) {
                        $replace = str_replace("(?P=$k)", htmlspecialchars($v, ENT_QUOTES, 'UTF-8', true), $replace);
                    } else {
                        $replace = str_replace("$$k", htmlspecialchars($v, ENT_QUOTES, 'UTF-8', true), $replace);
                    }
                }
                return $replace;
            };
            return preg_replace_callback($regex, $extendReplace, $subject);
        };

        foreach ($regex as $k => $v) {
            $c->vars = $myReplace($v, $replace[$k], $c->vars);
        }
        return $c;
    }

    public static function embedMedia($t, $restricted = true) {
        $c = clone $t;
        $rgxAttr = '(?:{(?P<css>((width|height|margin|padding)+:[0-9]+(px|%);)*)})?';
        $rgxCredit = '(?:\[(?P<credit>[^\]]*)\])?';
        $rgxUrl = '(?P<url>([0-9]+|[/?\pL0-9-_\.+&%:#=;,]+))';
        $regex = array(
            "@(!image$rgxAttr:$rgxUrl$rgxCredit)@u",
            "@!video$rgxAttr:$rgxUrl$rgxCredit@u",
            "@!music$rgxAttr:$rgxUrl$rgxCredit@u",
            "@!link$rgxAttr:$rgxUrl$rgxCredit@u"
        );

        $replace = array(
            '<div data-linked-media="(?P=url)" data-embed-image="(?P=url)" style="(?P=css)" data-credit="(?P=credit)"></div>',
            '<div data-embed-video="(?P=url)" data-credit="(?P=credit)" ></div>',
            '<div data-embed-music="(?P=url)" style="(?P=css)" data-credit="(?P=credit)"></div>',
            '<a data-embed-link="(?P=url)" href="(?P=url)" style="(?P=css)" data-credit="(?P=credit)">(?P=url)</a>'
        );
        //$c->vars = preg_replace($regex, $replace, $c->vars);

        $myReplace = function ($regex, $replace, $subject) {
            $extendReplace = function ($matches) use ($replace) {
                $string = array_shift($matches);

                foreach ($matches as $k => $v) {
                    if (!ctype_digit("$k")) {
                        $replace = str_replace("(?P=$k)", htmlspecialchars($v, ENT_QUOTES, 'UTF-8', true), $replace);
                    } else {
                        $replace = str_replace("$$k", htmlspecialchars($v, ENT_QUOTES, 'UTF-8', true), $replace);
                    }
                }
                return $replace;
            };
            return preg_replace_callback($regex, $extendReplace, $subject);
        };

        foreach ($regex as $k => $v) {
            $c->vars = $myReplace($v, $replace[$k], $c->vars);
        }
        return $c;
    }

    public static function htmlToText($t) {
        $c = clone $t;
        $c->vars = Filter::htmlToText($c->vars);
        return $c;
    }

    public static function extrait($t, $lenght = 120) {
        $t = clone $t;
        preg_match('/^.{0,' . $lenght . '}(?:.*?)\b/iu', $t->vars, $matches);
        $t->vars = '';
        foreach ($matches as $v) {
            if (strlen($t->vars + $v) < 120) {
                $t->vars = $v;
            }
        }
        return $t;
    }

    /*
     *  UTILS
     */

    /**
     * merge data in TemplateData object
     * 
     * @param unknown $t 
     * @param unknown $data data to merge
     * @return TemplateData new merged object 
     */
    public static function merge($t, $data) {
        if (!$data instanceof TemplateData) {
            $data = new TemplateData($data);
        }
        $c = clone $t;
        foreach ($data as $k => $v) {
            $c->vars[$k] = $v;
        }
        return $c;
    }

}
