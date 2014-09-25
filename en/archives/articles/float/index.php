<h2>Binary representation of single precision floating point numbers</h2>
<hr />
<p>
    <strong>Author:</strong> Jean-David Gadina<br />
    <strong>Copyright:</strong> &copy; <?php print date( 'Y', time() ); ?> Jean-David Gadina - www.xs-labs.com - All Rights Reserved<br />
    <strong>License:</strong> This article is published under the terms of the <?php print  XS_Menu::getInstance()->getPageLink( '/licenses/freebsd-documentation' ); ?><br />
    <strong>Source:</strong> <a href="http://ieeexplore.ieee.org/servlet/opac?punumber=4610933" title="IEEE 754">IEEE Standard for Floating-Point Arithmetic - IEEE 754</a>
</p>
<hr />
<h3>Table of contents</h3>
<ol>
    <li>
        <a href="#theory" title="Go to this section">Theory</a>
    </li>
    <li>
        <a href="#example" title="Go to this section">Example</a>
    </li>
    <li>
        <a href="#special-numbers" title="Go to this section">Special numbers</a>
        <ol>
            <li>
                <a href="#special-numbers-denormalized" title="Go to this section">Denormalized numbers</a>
            </li>
            <li>
                <a href="#special-numbers-zero" title="Go to this section">Zero</a>
            </li>
            <li>
                <a href="#special-numbers-infinity" title="Go to this section">Infinity</a>
            </li>
            <li>
                <a href="#special-numbers-nan" title="Go to this section">NaN</a>
            </li>
        </ol>
    </li>
    <li>
        <a href="#range" title="Go to this section">Range</a>
        <ol>
            <li>
                <a href="#range-normalized" title="Go to this section">Normalized numbers</a>
            </li>
            <li>
                <a href="#range-denormalized" title="Go to this section">Denormalized numbers</a>
            </li>
        </ol>
    </li>
    <li>
        <a href="#c-code" title="Go to this section">C code example</a>
    </li>
</ol>
<a name="theory"></a>
<h3>1. Theory</h3>
<p>
    Single precsion floating point numbers are usually called 'float', or 'real'. They are 4 bytes long, and are packed the following way, from left to right:
</p>
<ul>
    <li>Sign:     1 bit</li>
    <li>Exponent: 8 bits</li>
    <li>Mantissa: 23 bits</li>
</ul>
<table>
    <tr>
        <td>X</td>
        <td>XXXX XXXX</td>
        <td>XXX XXXX XXXX XXXX XXXX XXXXX</td>
    </tr>
    <tr>
        <th>Sign<br />1 bit</th>
        <th>Exponent<br />8 bits</th>
        <th>Mantissa<br />23 bits</th>
    </tr>
</table>
<p>
    The sign indicates if the number is positive or negative (zero for positive, one for negative).
</p>
<p>
    The real exponent is computed by substracting 127 to the value of the exponent field. It's the exponent of the number as it is expressed in the scientific notation.
</p>
<p>
    The full mantissa, which is also sometimes called significand, should be considered as a 24 bits value. As we are using scientific notation, there is an implicit leading bit (sometimes called the hidden bit), always set to 1, as there is never a leading 0 in the scientific notation.<br />
    For instance, you won't say <code>0.123 &middot; 10<span class="power">5</span></code> but <code>1.23 &middot; 10<span class="power">4</span></code>.
</p>
<p>
    The conversion is performed the following way:
</p>
<div class="code">
    <code>-1<span class="power">S</span> &middot; 1.M &middot; 2<span class="power">( E - 127 )</span></code>
</div>
<p>
    Where S is the sign, M the mantissa, and E the exponent.
</p>
<a name="example"></a>
<h3>2. Example</h3>
<p>
    For instance, <code>0100 0000 1011 1000 0000 0000 0000 0000</code>, which is <code>0x40B80000</code> in hexadecimal.
</p>
<table>
    <tr>
        <th>Hex</th>
        <td>4</td>
        <td class="alt">0</td>
        <td>B</td>
        <td class="alt">8</td>
        <td>0</td>
        <td class="alt">0</td>
        <td>0</td>
        <td class="alt">0</td>
    </tr>
    <tr>
        <th>Bin</th>
        <td>0100</td>
        <td class="alt">0000</td>
        <td>1011</td>
        <td class="alt">1000</td>
        <td>0000</td>
        <td class="alt">0000</td>
        <td>0000</td>
        <td class="alt">0000</td>
    </tr>
</table>
<table>
    <tr>
        <th>Sign</th>
        <th>Exponent</th>
        <th>Mantissa</th>
    </tr>
    <tr>
        <td>0</td>
        <td>1000 0001</td>
        <td>(1) 011 1000 0000 0000 0000 0000</td>
    </tr>
</table>
<ul>
    <li>The sign is <code>0</code>, so the number is positive.</li>
    <li>The exponent field is <code>1000 0001</code>, which is 129 in decimal. The real exponent value is then 129 - 127, which is 2.</li>
    <li>The mantissa with the leading 1 bit, is <code>1011 1000 0000 0000 0000 0000</code>.</li>
</ul>
<p>
    The final representation of the number in the binary scientific notation is:
</p>
<div class="code">
    <code>-1<span class="power">0</span> &middot; 1.0111 &middot; 2<span class="power">2</span></code>
</div>
<p>
    Mathematically, this means:
</p>
<div class="code">
    <code>1 &middot; ( 1 &middot; 2<span class="power">0</span> + 0 &middot; 2<span class="power">-1</span> + 1 &middot; 2<span class="power">-2</span> + 1 &middot; 2<span class="power">-3</span> + 1 &middot; 2<span class="power">-4</span> ) &middot; 2<span class="power">2</span></code><br />
    <code>( 2<span class="power">0</span> + 2<span class="power">-2</span> + 2<span class="power">-3</span> + 2<span class="power">-4</span> ) &middot; 2<span class="power">2</span></code><br />
    <code>2<span class="power">2</span> + 2<span class="power">0</span> + 2<span class="power">-1</span> + 2<span class="power">-2</span></code><br />
    <code>4 + 1 + 0.5 + 0.25</code>
</div>
<p>
    The floating point value is then 5.75.
</p>
<a name="special-numbers"></a>
<h3>3. Special numbers</h3>
<p>
    Depending on the value of the exponent field, some numbers can have special values. They can be:
</p>
<ul>
    <li>Denormalized numbers</li>
    <li>Zero</li>
    <li>Infinity</li>
    <li>NaN (not a number)</li>
</ul>
<a name="special-numbers-denormalized"></a>
<h4>3.1. Denormalized numbers</h4>
<p>
    If the value of the exponent field is 0 and the value of the mantissa field is greater than 0, then the number has to be treated as a denormalized number.<br />
    In such a case, the exponent is not -127, but -126, and the implicit leading bit is not 1 but 0.<br />
    That allows smaller numbers to be represented.
</p>
<p>
    The scientific notation for a denormalized number is:
</p>
<div class="code">
    <code>-1<span class="power">S</span> &middot;  0.M &middot; 2<span class="power">-126</span></code>
</div>
<a name="special-numbers-zero"></a>
<h4>3.2. Zero</h4>
<p>
    If the exponent and the mantissa fields are both 0, then the final number is zero. The sign bit is permitted, even if it does not have much sense mathematically, allowing a positive or a negative zero.<br />
    Note that zero can be considered as a denormalized number. In that case, it would be <code>0 &middot; 2<span class="power">-126</span></code>, which is zero.
</p>
<a name="special-numbers-infinity"></a>
<h4>3.3. Infinity</h4>
<p>
    If the value of the exponent field is 255 (all 8 bits are set) and if the value of the mantissa field is 0, the number is an infinity, either positive or negative, depending on the sign bit.
</p>
<a name="special-numbers-nan"></a>
<h4>3.4. NaN</h4>
<p>
    If the value of the exponent field is 255 (all 8 bits are set) and if the value of the mantissa field is not 0, then the value is not a number. The sign bit as no meaning in such a case.
</p>
<a name="range"></a>
<h3>3. Range</h3>
<p>
    The range depends if the number is normalized or not. Below are the ranges for that two cases:
</p>
<a name="range-normalized"></a>
<h4>3.1 Normalized numbers</h4>
<ul>
    <li><strong>Min:</strong> <code>±1.1754944909521E-38</code> / <code>±1.00000000000000000000001<span class="power">-126</span></code></li>
    <li><strong>Max:</strong> <code>±3.4028234663853E+38</code> / <code>±1.11111111111111111111111<span class="power">128</span></code></li>
</ul>
<a name="range-denormalized"></a>
<h4>3.2 Denormalized numbers</h4>
<ul>
    <li><strong>Min:</strong> <code>±1.4012984643248E-45</code> / <code>±0.00000000000000000000001<span class="power">-126</span></code></li>
    <li><strong>Max:</strong> <code>±1.1754942106924E-38</code> / <code>±0.11111111111111111111111<span class="power">-126</span></code></li>
</ul>
<a name="c-code"></a>
<h3>4. C code example</h3>
<p>
    Below is an example of a C program that will converts a binary number to its float representation:
</p>
<div class="code">
    <code class="source"><span class="code-pre">#include</span> <span class="code-string">&lt;stdlib.h&gt;</span></code><br />
    <code class="source"><span class="code-pre">#include</span> <span class="code-string">&lt;stdio.h&gt;</span></code><br />
    <code class="source"><span class="code-pre">#include</span> <span class="code-string">&lt;math.h&gt;</span></code><br />
    <code class="source"></code><br />
    <code class="source"><span class="code-comment">/**</span></code><br />
    <code class="source"><span class="code-comment"> * Converts a integer to its float representation</span></code><br />
    <code class="source"><span class="code-comment"> * </span></code><br />
    <code class="source"><span class="code-comment"> * This function converts a 32 bits integer to a single precision floating point</span></code><br />
    <code class="source"><span class="code-comment"> * number, as specified by the IEEE Standard for Floating-Point Arithmetic</span></code><br />
    <code class="source"><span class="code-comment"> * (IEEE 754). This standard can be found at the folowing address:</span></code><br />
    <code class="source"><span class="code-comment"> * {@link http://ieeexplore.ieee.org/servlet/opac?punumber=4610933}</span></code><br />
    <code class="source"><span class="code-comment"> * </span></code><br />
    <code class="source"><span class="code-comment"> * @param   unsigned long   The integer to convert to a floating point value</span></code><br />
    <code class="source"><span class="code-comment"> * @return  The floating point number</span></code><br />
    <code class="source"><span class="code-comment"> */</span></code><br />
    <code class="source"><span class="code-keyword">float</span> binaryToFloat( <span class="code-keyword">unsigned int</span> binary );</code><br />
    <code class="source"><span class="code-keyword">float</span> binaryToFloat( <span class="code-keyword">unsigned int</span> binary )</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-keyword">unsigned int</span> sign;</code><br />
    <code class="source">    <span class="code-keyword">int</span>          exp;</code><br />
    <code class="source">    <span class="code-keyword">unsigned int</span> mantissa;</code><br />
    <code class="source">    <span class="code-keyword">float</span>        floatValue;</code><br />
    <code class="source">    <span class="code-keyword">int</span>          i;</code><br />
    <code class="source">    </code><br />
    <code class="source">    <span class="code-comment">/* Gets the sign field */</span></code><br />
    <code class="source">    <span class="code-comment">/* Bit 0, left to right */</span></code><br />
    <code class="source">    sign = binary >> <span class="code-num">31</span>;</code><br />
    <code class="source">    </code><br />
    <code class="source">    <span class="code-comment">/* Gets the exponent field */</span></code><br />
    <code class="source">    <span class="code-comment">/* Bits 1 to 8, left to right */</span></code><br />
    <code class="source">    exp = ( ( binary >> <span class="code-num">23</span> ) & <span class="code-num">0xFF</span> );</code><br />
    <code class="source">    </code><br />
    <code class="source">    <span class="code-comment">/* Gets the mantissa field */</span></code><br />
    <code class="source">    <span class="code-comment">/* Bits 9 to 32, left to right */</span></code><br />
    <code class="source">    mantissa = ( binary & <span class="code-num">0x7FFFFF</span> );</code><br />
    <code class="source">    </code><br />
    <code class="source">    floatValue  = <span class="code-num">0</span>;</code><br />
    <code class="source">    i           = <span class="code-num">0</span>;</code><br />
    <code class="source">    </code><br />
    <code class="source">    <span class="code-comment">/* Checks the values of the exponent and the mantissa fields to handle special numbers */</span></code><br />
    <code class="source">    <span class="code-keyword">if</span>( exp == <span class="code-num">0</span> && mantissa == <span class="code-num">0</span> )</code><br />
    <code class="source">    {</code><br />
    <code class="source">        <span class="code-comment">/* Zero - No need for a computation even if it can be considered as a denormalized number */</span></code><br />
    <code class="source">        <span class="code-keyword">return</span> <span class="code-num">0</span>;</code><br />
    <code class="source">    }</code><br />
    <code class="source">    <span class="code-keyword">else if</span>( exp == <span class="code-num">255</span> && mantissa == <span class="code-num">0</span> )</code><br />
    <code class="source">    {</code><br />
    <code class="source">        <span class="code-comment">/* Infinity */</span></code><br />
    <code class="source">        <span class="code-keyword">return</span> <span class="code-num">0</span>;</code><br />
    <code class="source">    }</code><br />
    <code class="source">    <span class="code-keyword">else if</span>( exp == <span class="code-num">255</span> && mantissa != <span class="code-num">0</span> )</code><br />
    <code class="source">    {</code><br />
    <code class="source">        <span class="code-comment">/* Not a number */</span></code><br />
    <code class="source">        <span class="code-keyword">return</span> <span class="code-num">0</span>;</code><br />
    <code class="source">    }</code><br />
    <code class="source">    <span class="code-keyword">else if</span>( exp == <span class="code-num">0</span> && mantissa != <span class="code-num">0</span> )</code><br />
    <code class="source">    {</code><br />
    <code class="source">        <span class="code-comment">/* Denormalized number - Exponent is fixed to -126 */</span></code><br />
    <code class="source">        exp = <span class="code-num">-126</span>;</code><br />
    <code class="source">    }</code><br />
    <code class="source">    <span class="code-keyword">else</span></code><br />
    <code class="source">    {</code><br />
    <code class="source">        <span class="code-comment">/* Computes the real exponent */</span></code><br />
    <code class="source">        exp = exp - <span class="code-num">127</span>;</code><br />
    <code class="source">    </code><br />
    <code class="source">        <span class="code-comment">/* Adds the implicit bit to the mantissa */</span></code><br />
    <code class="source">        mantissa = mantissa | <span class="code-num">0x800000</span>;</code><br />
    <code class="source">    }</code><br />
    <code class="source">    </code><br />
    <code class="source">    <span class="code-comment">/* Process the 24 bits of the mantissa */</span></code><br />
    <code class="source">    <span class="code-keyword">for</span>( i = <span class="code-num">0</span>; i > <span class="code-num">-24</span>; i-- )</code><br />
    <code class="source">    {</code><br />
    <code class="source">        <span class="code-comment">/* Checks if the current bit is set */</span></code><br />
    <code class="source">        <span class="code-keyword">if</span>( mantissa & ( <span class="code-num">1</span> << ( i + <span class="code-num">23</span> ) ) )</code><br />
    <code class="source">        {</code><br />
    <code class="source">            <span class="code-comment">/* Adds the value for the current bit */</span></code><br />
    <code class="source">            <span class="code-comment">/* This is done by computing two raised to the power of the exponent plus the bit position */</span></code><br />
    <code class="source">            <span class="code-comment">/* (negative if it's after the implicit bit, as we are using scientific notation) */</span></code><br />
    <code class="source">            floatValue += ( <span class="code-keyword">float</span> )<span class="code-predefined">pow</span>( <span class="code-num">2</span>, i + exp );</code><br />
    <code class="source">        }</code><br />
    <code class="source">    }</code><br />
    <code class="source">    </code><br />
    <code class="source">    <span class="code-comment">/* Returns the final float value */</span></code><br />
    <code class="source">    <span class="code-keyword">return</span> ( sign == <span class="code-num">0</span> ) ? floatValue : -floatValue;</code><br />
    <code class="source">}</code><br />
    <code class="source"></code><br />
    <code class="source"><span class="code-keyword">int</span> main( <span class="code-keyword">void</span> )</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-predefined">printf</span>( <span class="code-string">"%f\n"</span>, <span class="code-ctag">binaryToFloat</span>( <span class="code-num">0x40B80000</span> ) );</code><br />
    <code class="source">    </code><br />
    <code class="source">    <span class="code-keyword">return</span> <span class="code-predefined">EXIT_SUCCESS</span>;</code><br />
    <code class="source">}</code>
</div>

