// Theme toggle functionality
function initializeTheme() {
  const themeToggle = document.getElementById("theme-toggle");
  const htmlElement = document.documentElement;

  if (!themeToggle) {
    return;
  }

  // Check for saved theme preference
  const savedTheme = localStorage.getItem("theme");
  if (
    savedTheme === "dark" ||
    (!savedTheme && window.matchMedia("(prefers-color-scheme: dark)").matches)
  ) {
    htmlElement.classList.add("dark");
  }

  themeToggle.addEventListener("click", () => {
    htmlElement.classList.toggle("dark");
    const currentTheme = htmlElement.classList.contains("dark")
      ? "dark"
      : "light";
    localStorage.setItem("theme", currentTheme);
  });
}

// Sidebar functionality
function initializeSidebar() {
  const collapseBtn = document.getElementById("collapse-btn");
  const sidebar = document.getElementById("sidebar");
  const mainContent = document.querySelector(".main-content");
  const sidebarToggle = document.getElementById("sidebar-toggle");

  if (collapseBtn && sidebar && mainContent) {
    // Check local storage for sidebar state
    const sidebarState = localStorage.getItem("sidebar");
    if (sidebarState === "collapsed") {
      sidebar.classList.add("collapsed");
      mainContent.classList.add("expanded");
    }

    collapseBtn.addEventListener("click", () => {
      sidebar.classList.toggle("collapsed");
      mainContent.classList.toggle("expanded");
      
      // Save state to localStorage
      if (sidebar.classList.contains("collapsed")) {
        localStorage.setItem("sidebar", "collapsed");
      } else {
        localStorage.setItem("sidebar", "expanded");
      }
    });
  }

  // Mobile sidebar toggle
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener("click", (e) => {
      e.stopPropagation();
      sidebar.classList.add("mobile-open");
    });
  }

  // Close sidebar when clicking outside on mobile
  document.addEventListener("click", (event) => {
    const isMobile = window.innerWidth < 992;
    if (
      isMobile &&
      sidebar &&
      !sidebar.contains(event.target) &&
      !sidebarToggle?.contains(event.target) &&
      sidebar.classList.contains("mobile-open")
    ) {
      sidebar.classList.remove("mobile-open");
    }
  });
}

// Dropdown functionality
function initializeDropdowns() {
  const dropdownToggles = document.querySelectorAll(
    ".notification-btn, .user-dropdown-toggle"
  );
  const dropdownMenus = document.querySelectorAll(".dropdown-menu");
  const closeDropdownBtns = document.querySelectorAll(".close-dropdown");

  // Toggle dropdown on click
  dropdownToggles.forEach((toggle) => {
    toggle.addEventListener("click", function (event) {
      event.preventDefault();
      event.stopPropagation();
      
      let dropdown = this.closest('.action-item')?.querySelector('.dropdown-menu');
      
      if (!dropdown) {
        return;
      }

      // Close all other dropdowns
      dropdownMenus.forEach((menu) => {
        if (menu !== dropdown && menu.classList.contains("show")) {
          menu.classList.remove("show");
        }
      });

      dropdown.classList.toggle("show");
    });
  });

  // Close dropdown when clicking the close button
  closeDropdownBtns.forEach((btn) => {
    btn.addEventListener("click", function (event) {
      event.stopPropagation();
      const dropdown = this.closest(".dropdown-menu");
      if (dropdown) {
        dropdown.classList.remove("show");
      }
    });
  });

  // Close dropdowns when clicking outside
  document.addEventListener("click", (event) => {
    if (!event.target.closest(".action-item")) {
      dropdownMenus.forEach((menu) => {
        menu.classList.remove("show");
      });
    }
  });

  // Prevent dropdown menu clicks from closing the dropdown
  dropdownMenus.forEach((menu) => {
    menu.addEventListener("click", (event) => {
      event.stopPropagation();
    });
  });
}

// Back to top button
function initializeBackToTop() {
  const backToTop = document.querySelector(".back-to-top");
  
  if (!backToTop) return;
  
  window.addEventListener("scroll", () => {
    if (window.pageYOffset > 300) {
      backToTop.classList.add("active");
    } else {
      backToTop.classList.remove("active");
    }
  });
  
  backToTop.addEventListener("click", (e) => {
    e.preventDefault();
    window.scrollTo({
      top: 0,
      behavior: "smooth"
    });
  });
}

// Submenu toggle
function initializeSubmenus() {
  const submenuToggles = document.querySelectorAll('[data-bs-toggle="collapse"]');
  
  submenuToggles.forEach(toggle => {
    toggle.addEventListener('click', function(e) {
      e.preventDefault();
      const target = document.querySelector(this.dataset.bsTarget);
      if (target) {
        target.classList.toggle('show');
        
        // Rotate arrow
        const arrow = this.querySelector('.fa-angle-right');
        if (arrow) {
          arrow.style.transform = target.classList.contains('show') ? 'rotate(90deg)' : '';
        }
      }
    });
  });
}

// Initialize all functionality when DOM is loaded
document.addEventListener("DOMContentLoaded", function() {
  initializeTheme();
  initializeSidebar();
  initializeDropdowns();
  initializeBackToTop();
  initializeSubmenus();
  
  // Handle window resize
  window.addEventListener("resize", () => {
    const sidebar = document.getElementById("sidebar");
    if (window.innerWidth >= 992 && sidebar) {
      sidebar.classList.remove("mobile-open");
    }
  });
});