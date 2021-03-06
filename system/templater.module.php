<?php
/*
 * JFTemplater 3.4beta
 * Dependencies: JFExceptions
 */
class JFTemplater
{
    //Metadata
    public $name = "JFTemplater";
    public $version = "3.4beta";
    //Directory with templates
    private $dir;
    //Content of current template, fills with method "load"
    private $content;
    //Variables for current template, fills with method "parse"
    private $vars;

    /**
     * Defines directory with templates
     *
     * @param string $dir
     *
     * @throws JFException
     */
    public function __construct( $dir = "" )
    {
        if ( $dir == "" )
        {
            $dir = JFConfig::get( "system/templatesDir" );
        }

        if ( $dir[strlen( $dir ) - 1] != DS )
        {
            $dir .= DS;
        }
        //If directory doesn't exist, throw exception
        if ( ! is_dir( $dir ) )
        {
            throw new JFException( JFError::FILE_NOT_FOUND );
        }
        //Set $this->dir
        $this->dir = $dir . DS;
    }

    /**
     * Loads template which should parse
     *
     * @param string $page Template to load without extension
     *
     * @throws JFException
     * @return void
     */
    public function load( $page )
    {
        //Check existing file and check, is it with .htm or .html extension
        if ( file_exists( $this->dir . $page . ".htm" ) )
        {
            $file = $this->dir . $page . ".htm";
        } elseif ( file_exists( $this->dir . $page . ".html" ) )
        {
            $file = $this->dir . $page . ".html";
        } else
        {
            throw new JFException( JFError::FILE_NOT_FOUND, "File {$this->dir}{$page}.htm(l) does not exist." );
        }

        //Fill $this->content with file contents
        $this->content = file_get_contents( $file );
    }

    /**
     * Start parsing
     *
     * @param array $vars Data to insert to template
     *
     * @return string mixed Filled array
     * @throws JFException
     */
    public function parse( $vars = array() )
    {
        //Vars must be an array!
        if ( ! is_array( $vars ) )
        {
            throw new JFException( JFError::TMPLTR_NOT_ARRAY_GIVEN );
        }
        $this->vars = $vars;
        unset( $vars );
        //Parsing file and returning final contents
        return $this->parseCode( $this->content );
    }

    //Main function that parses all the content
    private function parseCode( $code )
    {
        //I don't know, how is working this template, but it works.
        preg_match_all( '/\{(case|array|var|include)=\'(.*?)\'\}(((?:(?!\{\/?\1=\'\2\').)*)\{\/\1=\'\2\'\})?/s', $code, $matches );
        for ( $i = 0; $i < count( $matches[0] ); $i ++ )
        { //Parsing all the conditions with their methods
            switch ( $matches[1][$i] )
            {
                case "case":
                    $code = $this->parseCase( $matches[0][$i], $code );
                    break;
                case "array":
                    $code = $this->parseCycle( $matches[0][$i], $code );
                    break;
                case "var":
                    $code = $this->parseVar( $matches[0][$i], $code );
                    break;
                case "include":
                    $code = $this->parseInclude( $matches[0][$i], $code );
                    break;
            }
        }
        return $code;
    }

    //Insert variable. It's simple, I don't want to comment it...
    private function parseVar( $match, $code )
    {
        preg_match( '/\{var=\'(.*?)\'\}/', $match, $var );
        if ( ! isset( $this->vars[$var[1]] ) )
        {
            throw new JFException( JFError::TMPLTR_VAR_NOT_FOUND );
        }
        $code = str_replace( $match, $this->vars[$var[1]], $code );
        return $code;
    }

    //It loads given template and parse it with variables from $this->vars. Method is similar with method "load"
    private function parseInclude( $match, $code )
    {
        preg_match( '/\{include=\'(.*?)\'\}/', $match, $matches );
        if ( file_exists( $this->dir . $matches[1] . ".htm" ) )
        {
            $file = $this->dir . $matches[1] . ".htm";
        } elseif ( file_exists( $this->dir . $matches[1] . ".html" ) )
        {
            $file = $this->dir . $matches[1] . ".html";
        } else
        {
            throw new JFException( JFError::FILE_NOT_FOUND, "File " . $this->dir . $matches[1] . ".htm(l) does not exist" );
        }

        $content = file_get_contents( $file );
        //Recursive parsing of code
        $content = $this->parseCode( $content );
        $code = str_replace( $matches[0], $content, $code );
        return $code;
    }

    //This method parses cycles with multi-dimensional arrays
    private function parseCycle( $match, $code )
    {
        preg_match( '|\{array=\'(\w+)\'\}((?:(?!\{/?array).)*){/array=\'\1\'}|s', $match, $matches );
        if ( ! isset( $this->vars[$matches[1]] ) )
        {
            throw new JFException( JFError::TMPLTR_VAR_NOT_FOUND );
        }
        if ( ! is_array( $this->vars[$matches[1]] ) )
        {
            throw new JFException( JFError::TMPLTR_VAR_NOT_ARRAY );
        }
        if ( ! isset( $this->vars[$matches[1]][0] ) || ! is_array( $this->vars[$matches[1]][0] ) )
        {
            throw new JFException( JFError::TMPLTR_ARRAY_1DIM, "Array $" . $matches[1] . " is not multi-dimensional" );
        }
        $coden = "";
        //$coden (code new) is temp varible, which will have final content for replacing in $code. $temp is one-cycle variable, which will have only one entry and add it to $coden.
        for ( $c = 0; $c < count( $this->vars[$matches[1]] ); $c ++ )
        {
            $temp = $matches[2];
            //Replace all the entries with array
            foreach ( $this->vars[$matches[1]][$c] as $key => $value )
            {
                $temp = str_replace( "{" . $key . "}", $value, $temp );
            }
            $temp = $this->parseCode( $temp );
            $coden .= $temp;
        }
        $code = str_replace( $matches[0], $coden, $code );
        $code = $this->parseCode( $code );
        return $code;
    }

    //Parsing all the cases. VERY big method, will be shorted in time...
    private function parseCase( $match, $code )
    {
        preg_match( '/\{case=\'([0-9A-Za-z_]+)(==|!=|<=|>=|<|>|\|isset\|)([0-9A-Za-z_]+)\'\}((?:(?!\{\/?case=\'\1\2\3\').)*)\{\/case=\'\1\2\3\'\}/s', $match, $matches );
        switch ( $matches[2] )
        {
            case "==":
                if ( ! isset( $this->vars[$matches[1]] ) )
                {
                    throw new JFException( JFError::TMPLTR_VAR_NOT_FOUND, "Variable $" . $matches[1] . " does not exist" );
                }
                if ( isset( $this->vars[$matches[3]] ) )
                {
                    if ( $this->vars[$matches[1]] == $this->vars[$matches[3]] )
                    {
                        $matches[4] = $this->parseCode( $matches[4] );
                        $code = str_replace( $matches[0], $matches[4], $code );
                    } else
                    {
                        $code = str_replace( $matches[0], " ", $code );
                    }
                } else
                {
                    if ( $this->vars[$matches[1]] == $matches[3] )
                    {
                        $matches[4] = $this->parseCode( $matches[4] );
                        $code = str_replace( $matches[0], $matches[4], $code );
                    } else
                    {
                        $code = str_replace( $matches[0], " ", $code );
                    }
                }
                break;
            case "!=":
                if ( ! isset( $this->vars[$matches[1]] ) )
                {
                    throw new JFException( JFError::TMPLTR_VAR_NOT_FOUND, "Variable $" . $matches[1] . " does not exist" );
                }
                if ( isset( $this->vars[$matches[3]] ) )
                {
                    if ( $this->vars[$matches[1]] != $this->vars[$matches[3]] )
                    {
                        $matches[4] = $this->parseCode( $matches[4] );
                        $code = str_replace( $matches[0], $matches[4], $code );
                    } else
                    {
                        $code = str_replace( $matches[0], " ", $code );
                    }
                } else
                {
                    if ( $this->vars[$matches[1]] != $matches[3] )
                    {
                        $matches[4] = $this->parseCode( $matches[4] );
                        $code = str_replace( $matches[0], $matches[4], $code );
                    } else
                    {
                        $code = str_replace( $matches[0], " ", $code );
                    }
                }
                break;
            case "<=":
                if ( ! isset( $this->vars[$matches[1]] ) )
                {
                    throw new JFException( JFError::TMPLTR_VAR_NOT_FOUND, "Variable $" . $matches[1] . " does not exist" );
                }
                if ( isset( $this->vars[$matches[3]] ) )
                {
                    if ( $this->vars[$matches[1]] <= $this->vars[$matches[3]] )
                    {
                        $matches[4] = $this->parseCode( $matches[4] );
                        $code = str_replace( $matches[0], $matches[4], $code );
                    } else
                    {
                        $code = str_replace( $matches[0], " ", $code );
                    }
                } else
                {
                    if ( $this->vars[$matches[1]] <= $matches[3] )
                    {
                        $matches[4] = $this->parseCode( $matches[4] );
                        $code = str_replace( $matches[0], $matches[4], $code );
                    } else
                    {
                        $code = str_replace( $matches[0], " ", $code );
                    }
                }
                break;
            case ">=":
                if ( ! isset( $this->vars[$matches[1]] ) )
                {
                    throw new JFException( JFError::TMPLTR_VAR_NOT_FOUND, "Variable $" . $matches[1] . " does not exist" );
                }
                if ( isset( $this->vars[$matches[3]] ) )
                {
                    if ( $this->vars[$matches[1]] >= $this->vars[$matches[3]] )
                    {
                        $matches[4] = $this->parseCode( $matches[4] );
                        $code = str_replace( $matches[0], $matches[4], $code );
                    } else
                    {
                        $code = str_replace( $matches[0], " ", $code );
                    }
                } else
                {
                    if ( $this->vars[$matches[1]] >= $matches[3] )
                    {
                        $matches[4] = $this->parseCode( $matches[4] );
                        $code = str_replace( $matches[0], $matches[4], $code );
                    } else
                    {
                        $code = str_replace( $matches[0], " ", $code );
                    }
                }
                break;
            case "<":
                if ( ! isset( $this->vars[$matches[1]] ) )
                {
                    throw new JFException( JFError::TMPLTR_VAR_NOT_FOUND, "Variable $" . $matches[1] . " does not exist" );
                }
                if ( isset( $this->vars[$matches[3]] ) )
                {
                    if ( $this->vars[$matches[1]] < $this->vars[$matches[3]] )
                    {
                        $matches[4] = $this->parseCode( $matches[4] );
                        $code = str_replace( $matches[0], $matches[4], $code );
                    } else
                    {
                        $code = str_replace( $matches[0], " ", $code );
                    }
                } else
                {
                    if ( $this->vars[$matches[1]] < $matches[3] )
                    {
                        $matches[4] = $this->parseCode( $matches[4] );
                        $code = str_replace( $matches[0], $matches[4], $code );
                    } else
                    {
                        $code = str_replace( $matches[0], " ", $code );
                    }
                }
                break;
            case ">":
                if ( ! isset( $this->vars[$matches[1]] ) )
                {
                    throw new JFException( JFError::TMPLTR_VAR_NOT_FOUND, "Variable $" . $matches[1] . " does not exist" );
                }
                if ( isset( $this->vars[$matches[3]] ) )
                {
                    if ( $this->vars[$matches[1]] > $this->vars[$matches[3]] )
                    {
                        $matches[4] = $this->parseCode( $matches[4] );
                        $code = str_replace( $matches[0], $matches[4], $code );
                    } else
                    {
                        $code = str_replace( $matches[0], " ", $code );
                    }
                } else
                {
                    if ( $this->vars[$matches[1]] > $matches[3] )
                    {
                        $matches[4] = $this->parseCode( $matches[4] );
                        $code = str_replace( $matches[0], $matches[4], $code );
                    } else
                    {
                        $code = str_replace( $matches[0], " ", $code );
                    }
                }
                break;
            case "|isset|":
                if ( $matches[3] == "true" )
                {
                    if ( isset( $this->vars[$matches[1]] ) )
                    {
                        $matches[4] = $this->parseCode( $matches[4] );
                        $code = str_replace( $matches[0], $matches[4], $code );
                    } else
                    {
                        $code = str_replace( $matches[0], " ", $code );
                    }
                }
                if ( $matches[3] == "false" )
                {
                    if ( ! isset( $this->vars[$matches[1]] ) )
                    {
                        $matches[4] = $this->parseCode( $matches[4] );
                        $code = str_replace( $matches[0], $matches[4], $code );
                    } else
                    {
                        $code = str_replace( $matches[0], " ", $code );
                    }
                }
                break;
            default:
                throw new JFException( JFError::TMPLTR_NOT_VALID_CASE, $matches[2] . " is not a valid case" );
        }
        return $code;
    }

    public function uload()
    {
        $this->vars = NULL;
        $this->content = NULL;
        return true;
    }
}
