"use strict";
(() => {
  var __defProp = Object.defineProperty;
  var __getOwnPropNames = Object.getOwnPropertyNames;
  var __defNormalProp = (obj, key, value) => key in obj ? __defProp(obj, key, { enumerable: true, configurable: true, writable: true, value }) : obj[key] = value;
  var __esm = (fn, res) => function __init() {
    return fn && (res = (0, fn[__getOwnPropNames(fn)[0]])(fn = 0)), res;
  };
  var __commonJS = (cb, mod) => function __require() {
    return mod || (0, cb[__getOwnPropNames(cb)[0]])((mod = { exports: {} }).exports, mod), mod.exports;
  };
  var __publicField = (obj, key, value) => {
    __defNormalProp(obj, typeof key !== "symbol" ? key + "" : key, value);
    return value;
  };
  var __async = (__this, __arguments, generator) => {
    return new Promise((resolve, reject) => {
      var fulfilled = (value) => {
        try {
          step(generator.next(value));
        } catch (e) {
          reject(e);
        }
      };
      var rejected = (value) => {
        try {
          step(generator.throw(value));
        } catch (e) {
          reject(e);
        }
      };
      var step = (x) => x.done ? resolve(x.value) : Promise.resolve(x.value).then(fulfilled, rejected);
      step((generator = generator.apply(__this, __arguments)).next());
    });
  };

  // app/src/modules/Contact.ts
  var Contact, Contact_default;
  var init_Contact = __esm({
    "app/src/modules/Contact.ts"() {
      "use strict";
      Contact = class _Contact {
        constructor(form, submit, Param) {
          __publicField(this, "_form");
          __publicField(this, "_param");
          __publicField(this, "_submit");
          __publicField(this, "_overlay");
          __publicField(this, "submitForm", (evt) => __async(this, null, function* () {
            evt.preventDefault();
            const msg = yield this._submit(this._form);
            this.displayOverlay(msg);
          }));
          __publicField(this, "handleButton", (evt) => {
            evt.preventDefault();
            this._form.reset();
            this._overlay.content.style.opacity = "0";
            this._form.style.opacity = "1";
            this.toggleEffect();
            setTimeout(() => this._overlay.div.style.display = "none", 800);
          });
          this._form = form;
          this._submit = submit;
          this._param = Param;
          this.CreateOverlay();
          this._form.addEventListener("submit", this.submitForm);
          this.addOverlay2Form();
        }
        // creation de l'overlay
        CreateOverlay() {
          this._overlay = {
            div: document.createElement("div"),
            content: document.createElement("div"),
            txt: document.createElement("span"),
            btn: document.createElement("button"),
            toggle: false
          };
          this._overlay.div.appendChild(this._overlay.content);
          this._overlay.content.appendChild(this._overlay.txt);
          this._overlay.content.appendChild(document.createElement("br"));
          this._overlay.content.appendChild(this._overlay.btn);
          this._overlay.btn.innerHTML = this._param.btn.name;
          this._overlay.btn.addEventListener("click", this.handleButton);
          for (const cls of this._param.className)
            this._overlay.div.classList.add(cls);
          for (const cls of this._param.btn.class)
            this._overlay.btn.classList.add(cls);
          this._overlay.div.style.cssText = "position:absolute;        display:none; opacity:0;        transition:height 1s ease-out, width 0.8s, opacity 0.2s ease-out";
          this._overlay.content.style.cssText = "position:absolute;      opacity:0;      top:30%; left:50%;      transform:translate(-50%, -50%)";
          switch (this._param.effect) {
            case "top":
              this._overlay.div.style.height = "0px";
              this._overlay.div.style.width = "100%";
              this._overlay.div.style.top = "0";
              break;
            case "left":
              this._overlay.div.style.width = "0px";
              this._overlay.div.style.height = "100%";
              this._overlay.div.style.left = "0";
              this._overlay.div.style.top = "0";
              break;
            case "right":
              this._overlay.div.style.width = "0px";
              this._overlay.div.style.height = "100%";
              this._overlay.div.style.right = "0";
              this._overlay.div.style.top = "0";
              break;
            case "bottom":
              this._overlay.div.style.height = "0px";
              this._overlay.div.style.width = "100%";
              this._overlay.div.style.bottom = "0";
          }
        }
        /**
         * On attache l'overlay au formulaire
         */
        addOverlay2Form() {
          const parent = this._form.parentNode;
          if (!parent)
            return;
          parent.removeChild(this._form);
          const divRelative = document.createElement("div");
          divRelative.style.position = "relative";
          parent.appendChild(divRelative);
          divRelative.appendChild(this._form);
          divRelative.appendChild(this._overlay.div);
        }
        toggleEffect() {
          this._overlay.toggle = !this._overlay.toggle;
          switch (this._param.effect) {
            case "top":
            case "bottom":
              if (this._overlay.toggle)
                this._overlay.div.style.height = "100%";
              else
                this._overlay.div.style.height = "0px";
              break;
            case "left":
            case "right":
              if (this._overlay.toggle)
                this._overlay.div.style.width = "100%";
              else
                this._overlay.div.style.width = "0px";
              break;
          }
        }
        displayOverlay(msg) {
          this._overlay.div.style.display = "block";
          this._overlay.div.style.opacity = "1";
          this._form.style.opacity = "0";
          this._overlay.txt.innerHTML = msg;
          setTimeout(() => this._overlay.content.style.opacity = "1", 200);
          setTimeout(() => this.toggleEffect(), 50);
        }
        static bind(id, submit, overlay) {
          return __async(this, null, function* () {
            const el = document.getElementById(id);
            if (el instanceof HTMLFormElement)
              return new _Contact(el, submit, overlay);
          });
        }
      };
      Contact_default = Contact;
    }
  });

  // node_modules/fski-reveal/dist/Reveal.js
  var Reveal;
  var init_Reveal = __esm({
    "node_modules/fski-reveal/dist/Reveal.js"() {
      Reveal = class _Reveal {
        constructor(element, param) {
          this.timerLoad = 100;
          this.threshold = 0.3;
          this.tpsAnim = "1.3s";
          this.tpsAnimDisplay = "3s";
          this.infinite = false;
          this.decalage = 3;
          this.reveal = "";
          this.doInit = (transition = false) => {
            if (!this.element)
              return;
            if (!transition) {
              this.element.style.opacity = "0";
              this.element.style.visibility = "hidden";
              switch (this.reveal) {
                case "top":
                  this.element.style.transform = `translate(0, ${-1 * this.decalage}rem)`;
                  break;
                case "left":
                  this.element.style.transform = `translate(${-1 * this.decalage}rem)`;
                  break;
                case "right":
                  this.element.style.transform = `translate(${this.decalage}rem)`;
                  break;
                default:
                  break;
              }
            } else {
              if (this.reveal == "display")
                this.element.style.transition = `opacity ${this.tpsAnimDisplay} ease-out `;
              else
                this.element.style.transition = this.tpsAnim;
            }
          };
          this.doReveal = () => {
            if (!this.element)
              return;
            if (!this.isVisible)
              return;
            this.element.style.visibility = "inherit";
            this.element.style.opacity = "";
            if (this.reveal != "display")
              this.element.style.transform = "";
          };
          this.onResize = () => {
            this.doInit();
          };
          this.element = element;
          if (this.element.dataset.reveal)
            this.reveal = this.element.dataset.reveal;
          this.threshold = (param === null || param === void 0 ? void 0 : param.threshold) ? param.threshold : this.threshold;
          this.infinite = (param === null || param === void 0 ? void 0 : param.infinite) ? param.infinite : this.infinite;
          this.tpsAnimDisplay = (param === null || param === void 0 ? void 0 : param.tpsAnimDisplay) ? param.tpsAnimDisplay : this.tpsAnimDisplay;
          this.tpsAnim = (param === null || param === void 0 ? void 0 : param.tpsAnim) ? param.tpsAnim : this.tpsAnim;
          this.timerLoad = (param === null || param === void 0 ? void 0 : param.timerLoad) ? param.timerLoad : this.timerLoad;
          const observer = new IntersectionObserver((entries) => {
            for (const entry of entries) {
              if (entry.isIntersecting) {
                document.addEventListener("resize", this.onResize);
                this.isVisible = true;
                window.requestAnimationFrame(() => {
                  this.doReveal();
                });
              } else {
                this.isVisible = false;
                if (this.infinite)
                  this.doInit();
                window.removeEventListener("resize", this.onResize);
              }
            }
          }, {
            threshold: this.threshold
          });
          this.doInit();
          window.addEventListener("load", () => {
            if (!this.element)
              return;
            const el = this.element;
            this.doInit(true);
            setTimeout(() => observer.observe(el), this.timerLoad);
          });
        }
        static bind() {
          return __async(this, arguments, function* (param = {}) {
            return Array.from(document.querySelectorAll("[data-reveal]")).map((element) => {
              if (element instanceof HTMLElement)
                return new _Reveal(element, param);
            });
          });
        }
      };
    }
  });

  // app/src/modules/apiStaff.ts
  function postMsg(message, url = "/api/msg") {
    return __async(this, null, function* () {
      const Response = yield fetch(url, {
        method: "POST",
        headers: {
          "Content-Type": "application/json;charset=utf-8"
        },
        body: JSON.stringify(message)
      });
      if (Response.ok && Response.status == 201) {
        return true;
      }
      return false;
    });
  }
  var init_apiStaff = __esm({
    "app/src/modules/apiStaff.ts"() {
      "use strict";
    }
  });

  // app/src/app.ts
  var require_app = __commonJS({
    "app/src/app.ts"(exports) {
      init_Contact();
      init_Reveal();
      init_Contact();
      init_apiStaff();
      Reveal.bind({ infinite: true });
      Contact_default.bind(
        "contactForm",
        (form) => __async(exports, null, function* () {
          const data = { nom: "", mail: "", message: "" };
          const mail = form.elements.namedItem("mail");
          const nom = form.elements.namedItem("nom");
          const message = form.elements.namedItem("message");
          const sujet = form.elements.namedItem("sujet");
          if (nom instanceof HTMLInputElement)
            data.nom = nom.value;
          if (mail instanceof HTMLInputElement)
            data.mail = mail.value;
          if (sujet instanceof HTMLInputElement)
            data.sujet = sujet.value;
          if (message instanceof HTMLTextAreaElement)
            data.message = message.value;
          const answer = yield postMsg(data);
          if (answer)
            return "Merci, Thank you";
          return "Sorry something was wrong, please try later";
        }),
        {
          effect: "left",
          className: ["form-overlay", "bg-light"],
          btn: { name: "Ok", class: ["btn", "btn-secondary"] }
        }
      );
    }
  });
  require_app();
})();
