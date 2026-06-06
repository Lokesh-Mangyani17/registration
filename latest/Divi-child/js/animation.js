gsap.registerPlugin(ScrollTrigger, ScrollSmoother, Flip);


// SMOOTH SCROLLING 
const smoother = ScrollSmoother.create({
  wrapper: "#wrapper",
  content: "#content",
  smooth: 1,
  effects: true
});

//FADE IN CONTENT
gsap.to('.section-wrapper', {
  opacity: 1,
  duration: 0.1,
})


// Reveal H1
gsap.to("h1", {
  "--clip": "100%",
  duration: 0.6,
  delay: 0.3,
  ease: "Circ.easeInOut",
});

jQuery(function($){
	$('.blurb').on('click', function() {
		let $this = $(this),
			$content = '#' + $this.data('content');
		console.log($content);
		if ( $('body').find($content).length > 0 ) {
			smoother.scrollTo( $content, true, "center top" );	
		}
	});
});



// ANIMATE MAIN DESKTOP MENUS


// Select the <ul> element containing the menu items and the current item
const mainMenu = document.getElementById('main-menu');
const portalMenu = document.getElementById('portal-menu');
const menu = mainMenu ? mainMenu : portalMenu;

// Select the current menu item
let currentMenuItem = null
if (menu) {
  currentMenuItem = menu.querySelector('.current-menu-item');
}

// Create a new timeline for the animation
const ani = gsap.timeline({ paused: true });

// Add a Flip animation to move the bottom border from the current item to the clicked item
ani.to('.underline', { duration: 0.1, flip: { duration: 0.1, ease: "Circ.easeInOut" } })
  .to('.section-wrapper', { duration: 0.1, opacity: 0 });

// Add an event listener to each menu item that triggers the animation when clicked
if (menu) {
  menu.querySelectorAll('li').forEach((menuItem) => {
    menuItem.addEventListener('click', (event) => {
      // Prevent the default link behavior
      event.preventDefault();

      // Select the clicked menu item
      const clickedMenuItem = event.currentTarget;

      // Move the underline from the current item to the clicked item
      if (currentMenuItem) {
        ani.to(currentMenuItem.querySelector('.underline'), {
          x: clickedMenuItem.offsetLeft - currentMenuItem.offsetLeft,
          width: clickedMenuItem.offsetWidth,
        });
      } else {
        ani.set('.underline', {
          x: clickedMenuItem.offsetLeft,
          width: clickedMenuItem.offsetWidth,
        });
      }

      // Play the timeline
      ani.play();

      // Redirect to the clicked page after the animation has completed
      setTimeout(() => {
        window.location.href = clickedMenuItem.querySelector('a').href;
      }, 500);
    });
  });
}






// PIN / REVEAL the footer
let footerTrigger = null;
if (window.innerWidth > 768) {
  footerTrigger = ScrollTrigger.create({
    trigger: "footer",
    start: "bottom bottom",
    end: "max",
    pinSpacing: false,
    pin: true,
  });
}

// Refresh the ScrollTrigger when the footer is not pinned
if (footerTrigger &&!footerTrigger.isActive) {
  footerTrigger.refresh();
}



// NINJA FORM TIMEOUT
// Wait for a short delay after the window loads to ensure that the Ninja Forms element is fully loaded
setTimeout(() => {
  // Refresh the ScrollTrigger and start observing changes to the Ninja Forms element
  ScrollTrigger.refresh();

  // What is observer
  // observer.observe(nfForm, { childList: true, subtree: true });
}, 1000);



// STICKY PRICING - NOT ON MOBILE
if (!window.matchMedia("(max-width: 768px)").matches) {
  gsap.from('.sticky', {
    scrollTrigger: {
      trigger: ".product-device-details-wrapper",
      start: "top 5%",
      end: "bottom bottom",
      scrub: 4,
      pin: ".sticky",
      pinSpacing: false,
      markers: false,
    },
  });
}




// Accordion
const groups = gsap.utils.toArray(".accordion-group");

groups.forEach((el) => {
  el.addEventListener("click", () => toggleMenu(el));
});


function toggleMenu(el) {
  let state = Flip.getState(el.querySelector(".accordion-content"));
  el.classList.toggle("active");
  Flip.from(state, {
    duration: 0.5,
    ease: "Circ.easeInOut",
    onComplete: () => ScrollTrigger.refresh()
  });
}

ScrollTrigger.addEventListener("refresh", () => console.log("refresh"));





// Archive post grid/list display toggle
const allPosts = gsap.utils.toArray(".archive--grid");
const gridView = document.getElementById("grid-view");
const listView = document.getElementById("list-view");

if (gridView) {
  gridView.addEventListener("click", function () {
    if (!gridView.classList.contains("active")) {
      gridView.classList.add("active");
      listView.classList.remove("active");


      if (allPosts) {
        // toggle the view
        toggleView(allPosts[0], "grid");
      }
    }
  });
}

if (listView) {
  listView.addEventListener("click", function () {
    if (!listView.classList.contains("active")) {
      listView.classList.add("active");
      gridView.classList.remove("active");

      if (allPosts) {
        // toggle the view
        toggleView(allPosts[0], "list");
      }
    }
  });
}

function toggleView(el, view) {
  let states = [];
  el.querySelectorAll(".archive--post").forEach(post => {
    states.push(Flip.getState(post));
  });

  if (view === "list") {
    el.classList.add("list");
  } else {
    el.classList.remove("list");
  }

  states.forEach(state => {
    Flip.from(state, {
      duration: 0.5,
      ease: "Circ.easeInOut",
      onComplete: () => ScrollTrigger.refresh()
    });
  });
};



