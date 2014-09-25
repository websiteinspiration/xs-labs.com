<h2>Detecting idle time and activity with I/O Kit</h2>
<hr />
<p>
    <strong>Author:</strong> Jean-David Gadina<br />
    <strong>Copyright:</strong> &copy; <?php print date( 'Y', time() ); ?> Jean-David Gadina - www.xs-labs.com - All Rights Reserved<br />
    <strong>License:</strong> This article is published under the terms of the <?php print  XS_Menu::getInstance()->getPageLink( '/licenses/freebsd-documentation' ); ?><br />
</p>
<hr />
<p>
    It may be sometimes useful, from an application, to know if the user is currently interacting with the computer (or phone), or if he's away.<br />
    This article explains how to detect user's activity, on Mac OS X and on iOS.
</p>
<h3>I/O Kit</h3>
<p>
    There's in no direct way, with Cocoa, to detect if the computer is idle.<br />
    Idle means no interaction of the user with the computer. No mouse move nor keyboard entry, etc. Actions made solely by the OS are not concerned.
</p>
<p>
    The OS has of course access to that information, to allow a screensaver to activate, or to initiate computer sleep.
</p>
<p>
    To access this information, we'll have to use I/O Kit.<br />
    It consist in a collection of frameworks, libraries and tools used mainly to develop drivers for hardware components.
</p>
<p>
    In our case, we are going to use IOKitLib, a library that allows programmers to access hardware resources through the Mac OS kernel.
</p>
<p>
    As it's a low-level library, we need to code in C to use it.<br />
    So we are going to wrap the C code in an Objective-C class, to allow an easier and generic usage, as the C code may be harder to code for some programmers.
</p>
<h3>Project configuration</h3>
<p>
    Before writing the actual code, we are going to configure our XCode project, so it can use IOKitLib.<br />
    As it's a library, it must be linked with the final binary.
</p>
<p>
    Let's add a framework to our project:
</p>
<p>
    <img src="/uploads/image/archives/articles/iokit-idle/framework-add.png" alt="" width="377" height="267" />
</p>
<p>
    For a Mac OS X application, we can choose «IOKit.framework».<br />
    For iOS, this framework is not available, so we must choose «libIOKit.dylib».
</p>
<p>
    <img src="/uploads/image/archives/articles/iokit-idle/framework-list.png" alt="" width="338" height="534" />
</p>
<p>
    The framework is added to our project, and will now be linked with the application, after compilation time.
</p>
<p>
    <img src="/uploads/image/archives/articles/iokit-idle/framework-cocoa.png" alt="" width="250" height="120" /><br />
    <img src="/uploads/image/archives/articles/iokit-idle/framework-iphone.png" alt="" width="250" height="120" />
</p>
<h3>IOKitLib usage</h3>
<p>
    First of all, here are the reference manuals for I/O Kit:
</p>
<ul>
<li>
        <a href="http://developer.apple.com/library/mac/#documentation/Darwin/Reference/IOKit/">IOKitLib</a>
    </li>
<li>
        <a href="http://developer.apple.com/mac/library/documentation/DeviceDrivers/Conceptual/AccessingHardware/">Accessing Hardware</a>
    </li>
<li>
        <a href="http://developer.apple.com/mac/library/documentation/DeviceDrivers/Conceptual/IOKitFundamentals/">I/O Kit Fundamentals</a>
    </li>
</ul>
<p>
    Now let's create an Objective-C class that will detect the idle time:
</p>
<div class="code">
    <code class="source"><span class="code-keyword">#include</span> <span class="code-string">&lt;IOKit/IOKitLib.h&gt;</span></code><br />
    <code class="source"></code><br />
    <code class="source"><span class="code-keyword">@interface</span> IdleTime: NSObject</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-predefined">mach_port_t</span>   <span class="code-ctag">_ioPort</span>;</code><br />
    <code class="source">    <span class="code-predefined">io_iterator_t</span><span class="code-ctag"> _ioIterator</span>;</code><br />
    <code class="source">    <span class="code-predefined">io_object_t</span>   <span class="code-ctag">_ioObject</span>;</code><br />
    <code class="source">}</code><br />
    <code class="source"></code><br />
    <code class="source"><span class="code-keyword">@property</span>( <span class="code-keyword">readonly</span> ) <span class="code-predefined">uint64_t</span>   timeIdle;</code><br />
    <code class="source"><span class="code-keyword">@property</span>( <span class="code-keyword">readonly</span> ) <span class="code-predefined">NSUInteger</span> secondsIdle;</code><br />
    <code class="source"></code><br />
    <code class="source"><span class="code-keyword">@end</span></code>
</div>
<p>
    This class has three instance variables, which will be used to communicate with I/O Kit.<br />
    The variables' types are defined by the «IOKit/IOKitLib.h», which we are including.
</p>
<p>
    We are also defining two properties, that we'll use to access the idle time. The first one in nanoseconds, the second one in seconds.
</p>
<p>
    Here's the basic implementation of the class:
</p>
<div class="code">
    <code class="source"><span class="code-keyword">#include</span> <span class="code-string">"IdleTime.h"</span></code><br />
    <code class="source"></code><br />
    <code class="source"><span class="code-keyword">@implementation</span> IdleTime</code><br />
    <code class="source"></code><br />
    <code class="source">- ( <span class="code-keyword">id</span> )init</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-keyword">if</span>( (<span class="code-keyword"> self</span> = [ <span class="code-keyword">super</span> <span class="code-predefined">init</span> ] ) )</code><br />
    <code class="source">    {</code><br />
    <code class="source">        </code><br />
    <code class="source">    }</code><br />
    <code class="source">    </code><br />
    <code class="source">    <span class="code-keyword">return self</span>;</code><br />
    <code class="source">}</code><br />
    <code class="source"></code><br />
    <code class="source">- ( <span class="code-keyword">void</span> )dealloc</code><br />
    <code class="source">{</code><br />
    <code class="source">    [ <span class="code-keyword">super</span> <span class="code-predefined">dealloc</span> ];</code><br />
    <code class="source">}</code><br />
    <code class="source"></code><br />
    <code class="source">- ( <span class="code-predefined">uint64_t</span> )timeIdle</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-keyword">return</span> <span class="code-num">0</span>;</code><br />
    <code class="source">}</code><br />
    <code class="source"></code><br />
    <code class="source">- ( <span class="code-predefined">NSUInteger</span> )secondsIdle</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-predefined">uint64_t</span> time;</code><br />
    <code class="source">    </code><br />
    <code class="source">    time = <span class="code-keyword">self</span>.<span class="code-ctag">timeIdle</span>;</code><br />
    <code class="source">    </code><br />
    <code class="source">    <span class="code-keyword">return</span> ( <span class="code-predefined">NSUInteger</span> )( time >> <span class="code-num">30</span> );</code><br />
    <code class="source">}</code><br />
    <code class="source"></code><br />
    <code class="source"><span class="code-keyword">@end</span></code>
</div>
<p>
    We've got an «init» method that we will use to establish the base communication with I/O Kit, a «dealloc» method that will free the allocated resources, and a getter method for each property.
</p>
<p>
    The second method (secondsIdle) only takes the time in nanoseconds and converts it into seconds. To do so, we just have to divide the nano time by 10 raised to the power of 9. As we have integer values, a 30 right shift does exactly that, in a more efficient way.
</p>
<p>
    Now let's concentrate to the «init» method, and let's establish communication with I/O Kit, to obtain hardware informations.
</p>
<div class="code">
    <code class="source">- ( <span class="code-keyword">id</span> )init</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-predefined">kern_return_t</span> status;</code><br />
    <code class="source">    </code><br />
    <code class="source">    <span class="code-keyword">if</span>( ( <span class="code-keyword">self</span> = [ <span class="code-keyword">super</span> <span class="code-predefined">init</span> ] ) )</code><br />
    <code class="source">    {</code><br />
    <code class="source">        </code><br />
    <code class="source">    }</code><br />
    <code class="source">    </code><br />
    <code class="source">    <span class="code-keyword">return self</span>;</code><br />
    <code class="source">}</code>
</div>
<p>
    We a declaring a variable of type «kern_status» that we'll use to check the status of the I/O Kit communication, and to manage errors.<br />
    The following code is inside the «if» statement:
</p>
<div class="code">
    <code class="source">status = <span class="code-predefined">IOMasterPort</span>( <span class="code-keyword">MACH_PORT_NULL</span>, &#038;<span class="code-ctag">_ioPort</span> );</code>
</div>
<p>
    Here, we establish the connection with I/O Kit, on the default port (MACH_PORT_NULL).
</p>
<p>
    To know if the operation was successfull, we can check the value of the status variable with «KERN_SUCCESS»:
</p>
<div class="code">
    <code class="source"><span class="code-keyword">if</span>( status != <span class="code-keyword">KERN_SUCCESS</span> )</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-comment">/* Error management... */</span></code><br />
    <code class="source">}</code>
</div>
<p>
    I/O Kit has many services. The one we are going to use is «IOHID». It will allow us to know about user interaction.<br />
    In the following code, we get an iterator on the I/O Kit services, so we can access to IOHID.
</p>
<div class="code">
    <code class="source">status = <span class="code-predefined">IOServiceGetMatchingServices</span></code><br />
    <code class="source">(</code><br />
    <code class="source">    <span class="code-ctag">_ioPort</span>,</code><br />
    <code class="source">    <span class="code-predefined"> IOServiceMatching</span>( <span class="code-string">"IOHIDSystem"</span> ),</code><br />
    <code class="source">    &#038;<span class="code-ctag">_ioIterator</span></code><br />
    <code class="source">);</code>
</div>
<p>
    Now we can store the IOHID service:
</p>
<div class="code">
    <code class="source"><span class="code-ctag">_ioObject</span> = <span class="code-predefined">IOIteratorNext</span>( <span class="code-ctag">_ioIterator</span> );</code><br />
    <code class="source"></code><br />
    <code class="source"><span class="code-keyword">if</span> ( <span class="code-ctag">ioObject</span> == <span class="code-num">0</span> )</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-comment">/* Error management */</span></code><br />
    <code class="source">}</code><br />
    <code class="source"></code><br />
    <code class="source"><span class="code-predefined">IOObjectRetain</span>( <span class="code-ctag">_ioObject</span> );</code><br />
    <code class="source"><span class="code-predefined">IOObjectRetain</span>( <span class="code-ctag">_ioIterator</span> );</code>
</div>
<p>
    Here, we are doing a retain, so the objects won't be automatically freed.<br />
    So we'll have to release then in the «dealloc» method:
</p>
<div class="code">
    <code class="source">- ( <span class="code-keyword">void</span> )dealloc</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-predefined">IOObjectRelease</span>( <span class="code-ctag">_ioObject</span> );</code><br />
    <code class="source">    <span class="code-predefined">IOObjectRelease</span>( <span class="code-ctag">_ioIterator</span> );</code><br />
    <code class="source">    </code><br />
    <code class="source">    [ <span class="code-keyword">super</span> dealloc ];</code><br />
    <code class="source">}</code>
</div>
<p>
    Now the I/O Kit communication is established, and we have access to IOHID.<br />
    We can now use that service in the «timeIdle» method.
</p>
<div class="code">
    <code class="source">- ( <span class="code-predefined">uint64_t</span> )timeIdle</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-predefined">kern_return_t</span>          status;</code><br />
    <code class="source">    <span class="code-predefined">CFTypeRef</span>              idle;</code><br />
    <code class="source">    <span class="code-predefined">CFTypeID</span>               type;</code><br />
    <code class="source">    <span class="code-predefined">uint64_t</span>               time;</code><br />
    <code class="source">    <span class="code-predefined">CFMutableDictionaryRef</span> properties;</code><br />
    <code class="source">    </code><br />
    <code class="source">    properties = <span class="code-keyword">NULL</span>;</code>
</div>
<p>
    Let's start by declaring the variables we are going to use.
</p>
<p>
    First of all, we are going to access the IOHID properties.
</p>
<div class="code">
    <code class="source">status = <span class="code-predefined">IORegistryEntryCreateCFProperties</span></code><br />
    <code class="source">(</code><br />
    <code class="source">   <span class="code-ctag">_ioObject</span>,</code><br />
    <code class="source">   &#038;properties,</code><br />
    <code class="source">   <span class="code-predefined">kCFAllocatorDefault</span>,</code><br />
    <code class="source">   <span class="code-num">0</span></code><br />
    <code class="source">);</code>
</div>
<p>
    Here, we get a dictionary (similar to NSDictionary) in the «properties» variable.<br />
    We also get a kernel status, that we have to check, as usual.
</p>
<p>
    Now we can get the IOHID properties. The one we'll used is called «HIDIdleTime»:
</p>
<div class="code">
    <code class="source">idle = <span class="code-predefined">CFDictionaryGetValue</span>( properties, <span class="code-keyword">CFSTR</span>( <span class="code-string">"HIDIdleTime"</span> ) );</code><br />
    <code class="source">    </code><br />
    <code class="source"><span class="code-keyword">if</span>( !idle )</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-predefined">CFRelease</span>( ( <span class="code-predefined">CFTypeRef</span> )properties );</code><br />
    <code class="source">    </code><br />
    <code class="source">    <span class="code-comment">/* Error management */</span></code><br />
    <code class="source">}</code>
</div>
<p>
    If an error occurs, we have to release the «properties» object, in order to avoid a memory leak.
</p>
<p>
    A dictionary can contains several types of values, so we have to know the type of the «HIDIdleTime» property, before using it.
</p>
<div class="code">
    <code class="source">type = <span class="code-predefined">CFGetTypeID</span>( idle );</code>
</div>
<p>
    The property can be of type «number» or «data». To obtain the correct value, each case must be managed.
</p>
<div class="code">
    <code class="source"><span class="code-keyword">if</span>( type == <span class="code-predefined">CFDataGetTypeID</span>() )</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-predefined">CFDataGetBytes</span>( ( <span class="code-predefined">CFDataRef</span> )idle, <span class="code-predefined">CFRangeMake</span>( <span class="code-num">0</span>, <span class="code-keyword">sizeof</span>( time ) ), ( <span class="code-predefined">UInt8</span> * )&#038;time );</code><br />
    <code class="source">    </code><br />
    <code class="source">}</code><br />
    <code class="source"><span class="code-keyword">else if</span>( type == <span class="code-predefined">CFNumberGetTypeID</span>() )</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-predefined">CFNumberGetValue</span>( ( <span class="code-predefined">CFNumberRef</span> )idle, <span class="code-predefined">kCFNumberSInt64Type</span>, &#038;time );</code><br />
    <code class="source">}</code><br />
    <code class="source"><span class="code-keyword">else</span></code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-predefined">CFRelease</span>( idle );</code><br />
    <code class="source">    <span class="code-predefined">CFRelease</span>( ( <span class="code-predefined">CFTypeRef</span> )properties );</code><br />
    <code class="source">    </code><br />
    <code class="source">    <span class="code-comment">/* Error management */</span></code><br />
    <code class="source">}</code>
</div>
<p>
    Then we can release the objects, and return the value:
</p>
<div class="code">
    <code class="source"><span class="code-predefined">CFRelease</span>( idle );</code><br />
    <code class="source"><span class="code-predefined">CFRelease</span>( ( <span class="code-predefined">CFTypeRef</span> )properties );</code><br />
    <code class="source"></code><br />
    <code class="source"><span class="code-keyword">return</span> time;</code>
</div>
<p>
    The class is done. To use it, we just have to instantiate it and read the «secondsIdle» property (from a timer, for instance).
</p>
<h3>Demo</h3>
<p>
    Here's an example program using that class to display the idle time:
</p>
<p>
    <a href="/uploads/source/objc/idle.m">idle.m</a>
</p>
<p>
    To compile and execute it:
</p>
<div class="code">
    <code class="source">gcc -Wall -framework Cocoa -framework IOKit -o idle idle.m &#038;&#038; ./idle</code>
</div>
