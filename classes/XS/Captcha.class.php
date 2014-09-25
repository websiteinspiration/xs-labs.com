<?php

################################################################################
# Copyright (c) 2010, Jean-David Gadina <macmade@xs-labs.com>                  #
# All rights reserved.                                                         #
#                                                                              #
# Redistribution and use in source and binary forms, with or without           #
# modification, are permitted provided that the following conditions are met:  #
#                                                                              #
#  -   Redistributions of source code must retain the above copyright notice,  #
#      this list of conditions and the following disclaimer.                   #
#  -   Redistributions in binary form must reproduce the above copyright       #
#      notice, this list of conditions and the following disclaimer in the     #
#      documentation and/or other materials provided with the distribution.    #
#  -   Neither the name of 'Jean-David Gadina' nor the names of its            #
#      contributors may be used to endorse or promote products derived from    #
#      this software without specific prior written permission.                #
#                                                                              #
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"  #
# AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE    #
# IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE   #
# ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE    #
# LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR          #
# CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF         #
# SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS     #
# INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN      #
# CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)      #
# ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE   #
# POSSIBILITY OF SUCH DAMAGE.                                                  #
################################################################################

# $Id$

class XS_Captcha
{
    protected $_length      = 5;
    protected $_width       = 200;
    protected $_height      = 50;
    protected $_fgColor     = array( 150, 150, 150 );
    protected $_bgColor     = array( 240, 240, 240 );
    protected $_lineColor   = array( 230, 230, 230 );
    protected $_font        = 'consolas';
    protected $_fontSize    = 30;
    protected $_x           = 25;
    protected $_y           = 5;
    protected $_linePass    = 5;
    protected $_session     = NULL;
    
    public function __construct()
    {
        $this->_session = XS_Session::getInstance();
    }
    
    protected function _createString()
    {
        $string  = '';
        $counter = 0;
        
        while( $counter < $this->_length )
        {
            $random  = rand( 65, 90 );
            $char    = chr($random);
            $string .= $char;
            
            $counter++;
        }
        
        $this->_session->setData( __CLASS__, strtolower( $string ) );
        
        return $string;
    }
    
    public function createImage()
    {
        $text      = $this->_createString();
        $image     = imagecreate( $this->_width, $this->_height );
        $backColor = imagecolorallocate( $image, $this->_bgColor[ 0 ],   $this->_bgColor[ 1 ],   $this->_bgColor[ 2 ] );
        $textColor = imagecolorallocate( $image, $this->_fgColor[ 0 ],   $this->_fgColor[ 1 ],   $this->_fgColor[ 2 ] );
        $lineColor = imagecolorallocate( $image, $this->_lineColor[ 0 ], $this->_lineColor[ 1 ], $this->_lineColor[ 2 ] );
        
        imagefill( $image, 0, 0, $backColor );
        imagettftext ( $image, $this->_fontSize, 0, 45, 35, $textColor, __ROOTDIR__ . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . $this->_font . '.ttf', $text );
        
        for( $i = 0; $i < $this->_linePass; $i++)
        {
            $x1 = rand( 0, $this->_width - 1 );
            $y1 = rand( 0, round($this->_height / 10, 0 ) );
            $x2 = rand( 0, round($this->_width / 10, 0 ) );
            $y2 = rand( 0, $this->_height - 1 );
            
            imageline( $image, $x1, $y1, $x2, $y2, $lineColor );
            
            $x1 = rand( 0, $this->_width - 1 );
            $y1 = $this->_height - rand( 1, round( $this->_height / 10, 0 ) );
            $x2 = $this->_width  - rand( 1, round( $this->_width  / 10, 0 ) );
            $y2 = rand( 0, $this->_height - 1 );
            
            imageline( $image, $x1, $y1, $x2, $y2, $lineColor );

            $cx = rand( 0, $this->_width - 50 )  + 25;
            $cy = rand( 0, $this->_height - 50 ) + 25;
            $w  = rand( 1, 24 );
            
            imagearc( $image, $cx, $cy, $w, $w, 0, 360, $lineColor );
        }
        
        return $image;
    }
}
