(cookieLaw = {
    dId: "cookie-law-div",
    bId: "cookie-law-button",
    iId: "cookie-law-item",
    show: function (e) {
      if (localStorage.getItem(cookieLaw.iId)) return !1;
      var o = document.createElement("div"),
        i = document.createElement("p"),
        t = document.createElement("button");
      (i.innerHTML = e.msg),
        (t.id = cookieLaw.bId),
        (t.innerHTML = e.ok),
        (o.id = cookieLaw.dId),
        o.appendChild(t),
        o.appendChild(i),
        document.body.insertBefore(o, document.body.lastChild),
        t.addEventListener("click", cookieLaw.hide, !1);
    },
    hide: function () {
      (document.getElementById(cookieLaw.dId).outerHTML = ""),
        localStorage.setItem(cookieLaw.iId, "1");
    }
  }),
    cookieLaw.show({
      msg:
        "We use cookies to give you the best possible experience. By continuing to visit our website, you agree to the use of cookies as described in our <a href='/privacy'>Privacy Policy</a>",
      ok: "OK"
    });
  

    const cookieFade = new gsap.timeline;
    cookieFade.from("#cookie-law-div",
    {   
        delay:2,
        y:"20vh",
        opacity:0,
        duration:1,
        ease: "expo.inOut",
    },);
    
    
// LOGIN MODAL
var modalAnimation = gsap.timeline({ paused: true });

function openModal() {
  animateOpenModal();
  //var modalBtn = document.querySelectorAll(".modal-btn");
	
	document.querySelectorAll('.modal-btn').forEach(function(element) {
	   	  element.onclick = function (e) {
			  	e.preventDefault();
		  		modalAnimation.play();
			  	element.classList.add("open");
		  		document.body.classList.toggle("modal-open"); // Add the modal-open class to the body
		  }
	});
		/*
	console.log(modalBtn);
  if (modalBtn) {
	  
      modalBtn.onclick = function (e) {
      modalAnimation.play();
      modalBtn.classList.add("open");
      document.body.classList.toggle("modal-open"); // Add the modal-open class to the body
    };
  }
  */
  var modalCloseBtn = document.getElementById("modal-btn-close");
  if (modalCloseBtn) {
    modalCloseBtn.onclick = function (e) {
      modalAnimation.reverse();
		document.querySelectorAll('.modal-btn').forEach(function(element) {
			/*
	   	  element.onclick = function (e) {
		  		modalAnimation.play();
			  	element.classList.add("open");
		  		document.body.classList.toggle("modal-open"); // Add the modal-open class to the body
		  }
		  */
			element.classList.remove("open");
		});
      document.body.classList.remove("modal-open"); // Remove the modal-open class from the body
    };
  }
}

function animateOpenModal() {
  var loginModal = document.getElementById("hcp-modal");
  modalAnimation.to(loginModal, {
    duration: 0.5,
    ease: "power3.out",
    y: 0
  });
}

// init
openModal();
(function( $ ) {
	$(window).load(function(){
		openModal();
	});
}( jQuery ));