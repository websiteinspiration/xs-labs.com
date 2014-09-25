<div>
    <img src="/uploads/image/xeos/icon.png" alt="XEOS" width="200" height="200" class="img-right" />
</div>
<h2>Active sub-projects</h2>
<p>
    A complete operating system is obvisouly made of many parts.<br />
    Here are the actual projects which are currently beeing developed.
</p>
<div class="clearer"></div>
<div class="project-box-group">
    <div class="project-box-left">
        <h3 class="project-icons-ram">
            Memory allocator
        </h3>
        <p>
            XEOS allocates physical memory using a buddy memory allocator.<br />
            It will use virtual memory management and memory paging to manage memory for processes, including the kernel's own memory.<br />
            Memory management is actually the project with the highest priority.
         </p>
    </div>
    <div class="project-box-right">
        <h3 class="project-icons-stoplight">
            Scheduler
        </h3>
        <p>
            While XEOS currently doesn't have other processes besides the kernel, a scheduler will be needed soon.<br />
            It will use a preemptive scheduler to run processes and threads on available processor cores.
         </p>
    </div>
</div>
<div class="project-box-group">
    <div class="project-box-left">
        <h3 class="project-icons-library">
            C99 library
        </h3>
        <p>
            Whenever possible, XEOS tries to use C as a main language, for maximum compatibility.<br />
            A complete C99 standard library is currently beeing developped from scratch.<br />
            While processes will obvisouly use it, the XEOS kernel will also rely on it, to keep its code base clean and coherent.
         </p>
    </div>
    <div class="project-box-right">
        <h3 class="project-icons-gear">
            ACPI
        </h3>
        <p>
            ACPI stands for &laquo;Advanced Configuration and Power Interface&raquo;. It's an <a href="http://www.acpi.info/">open standard</a> for device configuration and power management, co-developed by Hewlett-Packard, Intel, Microsoft, Phoenix, and Toshiba.<br />
            XEOS aims to be fully compatible with the ACPI specification, and will therefore integrate the <a href="https://www.acpica.org/">ACPI Component Architecture (ACPICA)</a> project.
         </p>
    </div>
</div>
<div class="project-box-group">
    <div class="project-box-left">
        <h3 class="project-icons-disk-hard-disk">
            Filesystem
        </h3>
        <p>
            The XEOS kernel is actually loaded from a FAT-12 floppy drive, but it will obviously need to implement support for modern filesystems.<br />
            At the current development stage, ISO-9660, UFS and EXT-2 are considered.
         </p>
    </div>
    <div class="project-box-right">
        <h3 class="project-icons-os-mac-os-x">
            POSIX/SUS2
        </h3>
        <p>
            While not a priority yet, XEOS aims to POSIX/SUS2 compatibility. All kernel interfaces and sub-projects are currently developed with those standards in mind.
        </p>
    </div>
</div>
<div class="project-box-group">
    <div class="project-box-left">
        <h3 class="project-icons-preferences-panel">
            EFI boot
        </h3>
        <p>
            XEOS boots from BIOS-compatible systems.<br />
            In order to support modern architectures, an <a href="http://www.intel.com/content/www/us/en/architecture-and-technology/unified-extensible-firmware-interface/efi-homepage-general-technology.html">EFI</a>-compatible bootloader needs to be developped.
         </p>
    </div>
    <div class="project-box-right">
        <h3 class="project-icons-chip">
            ARM support
        </h3>
        <p>
            XEOS is compatible with the x86 and x86_64 CPU architectures (Intel, AMD).<br />
            While clearly not a priority, support for other CPU architectures, especially ARM, is considered.
         </p>
    </div>
</div>
<div class="project-box-group">
</div>
