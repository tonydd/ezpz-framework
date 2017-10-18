<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 14/10/17
 * Time: 16:43
 */
class Minify {

    protected $source;
    protected $type;

    /**
     * exec
     * Does the minification process
     */
    public function exec($content, $type) {

        $this->source = $content;
        $this->type = $type;

        switch($this->type) {
            case 'css':
                return $this->minifyCSS();
                break;
            case 'js':
                return $this->minifyJS();
                break;
        }

        return '';
    }

    /**
     * minifyCSS
     * Sets the minified data for CSS
     */
    private function minifyCSS() {
        return $this->strip_whitespaces($this->strip_linebreaks($this->strip_comments($this->source)));
    }

    /**
     * minifyJS
     * Sets the minified data for JavaScript
     */
    private function minifyJS() {
        return $this->superfixmyjs($this->strip_whitespaces($this->strip_linebreaks($this->strip_comments($this->source)))); // TODO
    }

    /**
     * strip_whitespaces
     * Removes any whitespace inside/betwen ;:{}[] chars. It also safely removes the extra space inside () parentheses
     *
     * @param: string
     */
    private function strip_whitespaces($string) {
        switch($this->type) {
            case 'css':
                $pattern = ';|:|,|\{|\}';
                break;
            case 'js':
                $pattern = ';|:|,|\{|\}|\[|\]';
                break;
        }
        return preg_replace('/\s*('.$pattern.')\s*/', '$1', preg_replace('/\(\s*(.*)\s*\)/', '($1)', $string));
    }

    /**
     * strip_linebreaks
     * Removes any line break in the form of newline, carriage return, tab and extra spaces
     *
     * @param: string
     */
    private function strip_linebreaks($string) {
        return preg_replace('/(\\\?[\n\r\t]+|\s{2,})/', '', $string);
    }

    /**
     * strip_comments
     * Removes all the known comment types from the source
     *
     * @param: string
     */
    private function strip_comments($string) {
        // Don't touch anything inside a quote or regex
        $protected = '(?<![\\\/\'":])';
        // Comment types
        $multiline = '\/\*[^*]*\*+([^\/][^\*]*\*+)*\/'; // /* comment */
        $html = '<!--([\w\s]*?)-->'; // <!-- comment -->
        $ctype = '\/\/.*'; // //comment (Yo Dawg)!
        // The pattern
        $pattern = $protected;
        switch($this->type) {
            case 'css':
                $pattern .= $multiline;
                break;
            case 'js':
                $pattern .= '('.$multiline.'|'.$html.'|'.$ctype.')';
                break;
        }
        return preg_replace('#'.$pattern.'#', '', $string);
    }

    /**
     * superfixmyjs
     * Attemps to fix the missing syntax for JavaScript
     * Note: It doesn't fix stupidity.
     *
     * @param: string
     */
    private function superfixmyjs($string) {
        // mmkay fix the misisng ; in javascript objects
        $pattern = '#[=|:][\[\{].*?(?=[\}\]][^,\}\]])[\}|\]](.{1})#';
        preg_match_all($pattern, $string, $matches);

        foreach($matches[1] as $key => $value) {
            if($value !== ";") {
                $source[] = $matches[0][$key];
                $replace[] = substr($matches[0][$key], 0, -1).';'.$value;
            }
        }

        return isset($replace) ? str_replace($source, $replace, $string) : $string;

    }

}