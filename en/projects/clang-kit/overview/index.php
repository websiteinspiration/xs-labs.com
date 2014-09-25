<div>
    <img src="/uploads/image/clang-kit/icon.png" alt="ClangKit" width="200" height="200" class="img-right" />
</div>
<h2>Objective-C (Foundation) frontend to LibClang</h2>
<p>
    ClangKit provides an Objective-C frontend to LibClang.<br />
    Source tokenization, diagnostics and fix-its are actually implemented.
</p>
<p>
    ClangKit is intended to be used as a private framework, in an application's bundle.<br />
    Possible applications includes:
</p>
<ul>
      <li>Source code syntax highlighting</li>
      <li>Source code tokenization</li>
      <li>Source code diagnostics</li>
      <li>Source code static analysis</li>
</ul>
<h3>Language support</h3>
<p>
    The project actually supports parsing C, C++, Objective-C and Objective-C++ source code.
</p>
<h3>iOS note</h3>
<p>
    The project is not yet compatible with iOS, but everything should be fine, as LibClang is actually compiled as a static library.<br />
</p>
<h3>License</h3>
<p>
    ClangKit is published under the terms of the <?php print XS_Menu::getInstance()->getPageLink( "/licenses/boost/", "BOOST license" ); ?>.
</p>
