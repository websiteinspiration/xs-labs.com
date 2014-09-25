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

class XS_Image_Gallery
{
    protected $_group   = '';
    protected $_path    = '';
    protected $_content = NULL;
    protected $_thumbs  = array();
    
    public function __construct( $path )
    {
        $this->_content            = new XS_Xhtml_Tag( 'div' );
        $this->_content[ 'class' ] = 'gallery';
        $this->_path               = __ROOTDIR__ . $path;
        $this->_thumbs             = glob( $this->_path . 'thumb-*.{jpg,png,jpeg,gif}', GLOB_BRACE );
        $this->_group              = md5( uniqid( microtime(), true ) );
        
        foreach( $this->_thumbs as $thumb ) {
            
            $src  = str_replace( __ROOTDIR__, '', $thumb );
            $size = getimagesize( $thumb );
            $name = str_replace( $this->_path, '', $thumb );
            
            $div  = $this->_content->div;
            $link = $div->a;
            $img  = $link->img;
            
            $div[ 'class' ]  = 'left';
            $link[ 'class' ] = 'fancyBox';
            $link[ 'href' ]  = str_replace( 'thumb-', '', $src );
            $link[ 'title' ] = str_replace( 'thumb-', '', $name );
            $link[ 'rel' ]   = $this->_group;
            $img[ 'src' ]    = $src;
            $img[ 'alt' ]    = $name;
            $img[ 'width' ]  = $size[ 0 ];
            $img[ 'height' ] = $size[ 1 ];
        }
    }
    
    public function __toString()
    {
        return ( string )$this->_content;
    }
}
