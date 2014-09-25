<h2>Reference counting in ANSI-C</h2>
<hr />
<p>
    <strong>Author:</strong> Jean-David Gadina<br />
    <strong>Copyright:</strong> &copy; <?php print date( 'Y', time() ); ?> Jean-David Gadina - www.xs-labs.com - All Rights Reserved<br />
    <strong>License:</strong> This article is published under the terms of the <?php print  XS_Menu::getInstance()->getPageLink( '/licenses/freebsd-documentation' ); ?><br />
</p>
<hr />
<h3>About</h3>
<p>
    Memory management can be a hard task when coding a C program.<br />
    Some higher level programming languages provide other ways to manage memory.<br />
    Main variants are garbage collection, and reference counting.<br />
    This article will teach you how to implement a reference counting memory management system in C.
</p>
<p>
    Personally, as a C and Objective-C developer, I love the reference counting way.<br />
    It implies the notion of ownership on objects.
</p>
<h3>Objective-C example</h3>
<p>
    For instance, in Objective-C, when you creates an object using the alloc, or copy methods, you own the object. It means you'll have to release your object, so the memory can be reclaimed.
</p>
<p>
    Objects can also be retained. In such a case they must be released too.
</p>
<p>
    Objects get by convenience methods are not owned by the caller, so there's no need to release them, as it will be done by someone else.
</p>
<p>
    For instance, in Objective-C:
</p>
<div class="code">
    <code class="source"><span class="code-predefined">NSArray</span> * object1 = [ <span class="code-predefined">NSArray</span> <span class="code-predefined">array</span> ];</code><br />
    <code class="source"><span class="code-predefined">NSArray</span> * object2 = [ [ <span class="code-predefined">NSArray</span> <span class="code-predefined">alloc</span> ] <span class="code-predefined">init</span> ];</code><br />
    <code class="source"><span class="code-predefined">NSArray</span> * object3 = [ [ [ <span class="code-predefined">NSArray</span> <span class="code-predefined">array</span> ] <span class="code-predefined">retain</span> ] <span class="code-predefined">retain</span> ];</code>
</div>
<p>
    Here, the object2 variable will need to be released, as we allocated it explicitly.<br />
    The object3 variable will need to be released twice, since we retained it twice.
</p>
<div class="code">
    <code class="source">[ object2 <span class="code-predefined">release</span> ];</code><br />
    <code class="source">[ [ object3 <span class="code-predefined">release</span> ] <span class="code-predefined">release</span> ];</code>
</div>
<h3>C implementation</h3>
<p>
    As a C coder, I've implemented this with ANSi-C.<br />
    Here are some explanations.
</p>
<p>
    First of all, we are going to define a structure for our memory records.<br />
    The structure will look like this:
</p>
<div class="code">
    <code class="source"><span class="code-keyword">typedef struct</span></code><br />
    <code class="source">{</code><br />
    <code class="source">	<span class="code-keyword">unsigned int</span> retainCount</code><br />
    <code class="source">	<span class="code-keyword">void</span>       * data;</code><br />
    <code class="source">}</code><br />
    <code class="source">MemoryObject;</code>
</div>
<p>
    Here, we are storing the retain count of the memory object. A retain will increment it, and a release decrement it. When it reaches 0, the object will be freed.
</p>
<p>
    We'll also need a custom allocation function:
</p>
<div class="code">
    <code class="source"><span class="code-keyword">void</span> * Alloc( <span class="code-keyword">size_t</span> size )</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-ctag">MemoryObject</span> * o;</code><br />
    <code class="source">    </code><br />
    <code class="source">    o = ( <span class="code-ctag">MemoryObject</span> * )<span class="code-predefined">calloc</span>( <span class="code-keyword">sizeof</span>( MemoryObject ) + size, <span class="code-num">1</span> );</code>
</div>
<p>
    Here, allocate space for our memory object structure, plus the user requested size.<br />
    We are not going to return the memory object, so we need some calculation here.
</p>
<p>
    First of all, let's declare a char pointer, that will point to our allocated memory object structure:
</p>
<div class="code">
    <code class="source"><span class="code-keyword">char</span> * ptr = ( <span class="code-keyword">char</span> * )o;</code>
</div>
<p>
    Then, we can get the location of the user defined data by adding the size of the memory object structure:
</p>
<div class="code">
    <code class="source">ptr += <span class="code-keyword">sizeof</span>( <span class="code-ctag">MemoryObject</span> );</code>
</div>
<p>
    Then, we can set our data pointer, et set the retain count to 1.
</p>
<div class="code">
    <code class="source">o-><span class="code-ctag">data</span>        = ptr;</code><br />
    <code class="source">o-><span class="code-ctag">retainCount</span> = <span class="code-num">1</span>;</code>
</div>
<p>
    Now we'll return to pointer to the user data, so it doesn't have to know about our memory object structure.
</p>
<div class="code">
    <code class="source"><span class="code-keyword">return</span> ptr;</code>
</div>
<p>
    Here's the full function:
</p>
<div class="code">
    <code class="source"><span class="code-keyword">void</span> * Alloc( <span class="code-keyword">size_t</span> size )</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-ctag">MemoryObject</span> * o;</code><br />
    <code class="source">    <span class="code-keyword">char</span>         * ptr;</code><br />
    <code class="source">    </code><br />
    <code class="source">    o              = ( <span class="code-ctag">MemoryObject</span> * )<span class="code-predefined">calloc</span>( <span class="code-keyword">sizeof</span>( MemoryObject ) + size, <span class="code-ctag">1</span> );</code><br />
    <code class="source">    ptr            = ( <span class="code-keyword">char</span> * )o;</code><br />
    <code class="source">    ptr           += <span class="code-keyword">sizeof</span>( <span class="code-ctag">MemoryObject</span> );</code><br />
    <code class="source">    o-><span class="code-ctag">retainCount</span> = <span class="code-num">1</span>;</code><br />
    <code class="source">    o-><span class="code-ctag">data</span>        = ptr;</code><br />
    <code class="source">    </code><br />
    <code class="source">    <span class="code-keyword">return</span> ( <span class="code-keyword">void</span> * )ptr;</code><br />
    <code class="source">}</code>
</div>
<p>
    This way, we return the user defined allocated size, and we are hiding our structure before that data.
</p>
<p>
    To retrieve our data, we simply need to subtract the size of the MemoryObject structure.
</p>
<p>
    For example, here's the Retain function:
</p>
<div class="code">
    <code class="source"><span class="code-keyword">void</span> Retain( <span class="code-keyword">void</span> * ptr )</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-ctag">MemoryObject</span> * o;</code><br />
    <code class="source">    <span class="code-keyword">char</span>         * cptr;</code><br />
    <code class="source">    </code><br />
    <code class="source">    cptr  = ( <span class="code-keyword">char</span> * )ptr;</code><br />
    <code class="source">    cptr -= <span class="code-keyword">sizeof</span>( <span class="code-ctag">MemoryObject</span> );</code><br />
    <code class="source">    o     = ( <span class="code-ctag">MemoryObject</span> * )cptr;</code><br />
    <code class="source">    </code><br />
    <code class="source">    o-><span class="code-ctag">retainCount</span>++:</code><br />
    <code class="source">}</code>
</div>
<p>
    We are here retrieving our MemoryObject structure, by subtracting the size of it to the user pointer. Once done, we can increase the retain count by one.
</p>
<p>
    Same thing is done for the Release function:
</p>
<div class="code">
    <code class="source"><span class="code-keyword">void</span> Release( <span class="code-keyword">void</span> * ptr )</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-ctag">MemoryObject</span> * o;</code><br />
    <code class="source">    <span class="code-keyword">char</span>         * cptr;</code><br />
    <code class="source">    </code><br />
    <code class="source">    cptr  = ( <span class="code-keyword">char</span> * )ptr;</code><br />
    <code class="source">    cptr -= <span class="code-keyword">sizeof</span>( <span class="code-ctag">MemoryObject</span> );</code><br />
    <code class="source">    o     = ( <span class="code-ctag">MemoryObject</span> * )cptr;</code><br />
    <code class="source">    </code><br />
    <code class="source">    o-><span class="code-ctag">retainCount</span>--:</code><br />
    <code class="source">    </code><br />
    <code class="source">    if( o-><span class="code-ctag">retainCount</span> == <span class="code-num">0</span> )</code><br />
    <code class="source">    {</code><br />
    <code class="source">        <span class="code-predefined">free</span>( o );</code><br />
    <code class="source">    }</code><br />
    <code class="source">}</code>
</div>
<p>
    When the retain count reaches zero, we can free the object.
</p>
<p>
    That's all. We now have a reference counting memory management in C.<br />
    All you have to do is call Alloc to create an object, Retain if you need to, and Release when you don't need the object anymore.<br />
    It may have been retained by another function, but then you don't have to care if it will be freed or not, as you don't own the object anymore.
</p>
