<?php

class XS_Css_Minifier
{
    protected $_css          = '';
    protected $_path         = '';
    protected $_basePath     = '';
    protected $_comment      = '';
    protected $_colors       = array();
    protected $_colorsSimple = array();
    
    public function __construct()
    {
        for( $i = 0; $i < 16; $i++ )
        {
            $c                     = dechex( $i );
            $this->_colors[]       = '/#' . str_repeat( $c, 6 ) . '/i';
            $this->_colorsSimple[] = '#'  . str_repeat( $c, 3 );
        }
    }
    
    public function __toString()
    {
        $css = '/* <![CDATA[ */'
             . chr( 10 )
             . chr( 10 )
             . ( ( $this->_comment ) ? $this->_comment . chr( 10 ) . chr( 10 ) : '' )
             . $this->_css
             . chr( 10 )
             . chr( 10 )
             . '/* ]]> */';
        
        return $css;
    }
    
    public function setBaseDirectory( $path )
    {
        $this->_path = ( string )$path;
        
        if( substr( $this->_path, -1, 1 ) !== '/' )
        {
            $this->_path .= '/';
        }
        
        $this->_basePath = dirname( $_SERVER[ 'SCRIPT_NAME' ] );
        
        if( substr( $this->_basePath, -1, 1 ) !== '/' )
        {
            $this->_basePath .= '/';
        }
    }
    
    public function import( $path )
    {
        $path = ( string )$path;
        $path = $this->_path . $path;
        
        if( !file_exists( $path ) )
        {
            return;
        }
        
        $css = file_get_contents( $path );
        
        $css = preg_replace( '|url\(\s?(["\'])([^ )]+) ?\)([; ])|', 'url(\1' . $this->_basePath . '\2)\3', $css );
        $css = preg_replace( $this->_colors, $this->_colorsSimple, $css );
        
        $css = preg_replace( '#\s+#', ' ', $css );
        $css = preg_replace( '#/\*.*?\*/#s', '', $css );
        
        $css = str_replace( '; ', ';', $css );
        $css = str_replace( ': ', ':', $css );
        $css = str_replace( ' {', '{', $css );
        $css = str_replace( '{ ', '{', $css );
        $css = str_replace( ', ', ',', $css );
        $css = str_replace( '} ', '}', $css );
        $css = str_replace( ';}', '}', $css );
        
        $this->_css .= trim( $css );
    }
    
    public function output()
    {
        header( 'Content-type: text/css' );
        print $this;
        exit();
    }
    
    public function setComment( $text )
    {
        $lines  = explode( chr( 10 ), $text );
        $length = 0;
        
        foreach( $lines as $pos => $line )
        {
            $lines[ $pos ] = utf8_decode( $line );
            $lineLength    = strlen( utf8_decode( $line ) );
            $length        = ( $lineLength > $length ) ? $lineLength : $length;
        }
        
        $sep     = str_repeat( '#', $length + 4 );
        $comment = $sep . chr( 10 );
        
        foreach( $lines as $line )
        {
            $lineLength = strlen( $line );
            $comment   .= '# '
                       .  str_repeat( ' ', floor( ( $length - $lineLength ) / 2 ) )
                       .  $line
                       .  str_repeat( ' ', ceil( ( $length - $lineLength ) / 2 ) )
                       .  ' #'
                       .  chr( 10 );
        }
        
        $comment .= $sep;
        
        $this->_comment = '/*'
                        . chr( 10 )
                        . chr( 10 )
                        . $comment
                        . chr( 10 )
                        . chr( 10 )
                        . '*/';
    }
}
