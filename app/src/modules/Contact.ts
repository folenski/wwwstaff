/**
 * Class pour gérer un contact
 *
 * 06/01/2022 - Version initiale
 * 12/01/2022 - La recuperation des champs est maintenant dynamique et gestion de la direction
 *              de la div de retour
 * 13/07/2022 - écriture en TS
 */

interface ContactParam {
  idform: string;
  url?: string;
  errorMsg?: string;
  overlay?: {
    from: string;
    class: string[];
  };
  btn?: {
    lib: string;
    class: string[];
  };
}

class Contact {
  private _element!: HTMLFormElement;
  private _Param!: ContactParam;

  private _ParamDefault: ContactParam = {
    idform: "none",
    url: "/api/msg",
    errorMsg: "An error was encountered<br/>Please try again later",
    overlay: { from: "bottom", class: ["form-overlay"] },
    btn: { lib: "Ok", class: ["btn"] },
  };

  private _overlay!: HTMLDivElement;
  private _overlayContent!: HTMLDivElement;
  private _overlayTxt!: HTMLSpanElement;
  private _transition = false;

  constructor(element: HTMLFormElement, Param: ContactParam) {
    this._element = element;
    this._Param = { ...this._ParamDefault, ...Param };

    this.createOverlay();
    this.initOverlay();

    this._overlay.addEventListener("transitionend", this.transitionendOverlay);
    this._element.addEventListener("submit", this.submitForm);
  }

  /**
   * Création de l'overlay pour le retour du fetch
   */
  createOverlay() {
    this._overlay = document.createElement("div");
    this._overlay.style.display = "none";
    this._overlay.style.opacity = "0";

    const parent = this._element.parentElement;
    if (parent) parent.appendChild(this._overlay);

    // on ajoute les classes
    if (this._Param.overlay)
      this._Param.overlay.class.forEach((cl) =>
        this._overlay.classList.add(cl)
      );

    this._overlayContent = document.createElement("div");
    this._overlay.appendChild(this._overlayContent);

    this._overlayTxt = document.createElement("span");
    this._overlayContent.appendChild(this._overlayTxt);
    this._overlayContent.appendChild(document.createElement("br"));

    // Gestion du bouton retour
    if (this._Param.btn) {
      const btnBack = document.createElement("button");
      btnBack.innerHTML = this._Param.btn.lib;
      this._Param.btn.class.forEach((cl) => btnBack.classList.add(cl));
      btnBack.addEventListener("click", this.handleBack);
      this._overlayContent.appendChild(btnBack);
    }
  }

  initOverlay() {
    if (!this._Param.overlay) return;

    this._overlayContent.style.opacity = "0";
    this._transition = true;

    switch (this._Param.overlay.from) {
      case "top":
        this._overlay.style.height = "0";
        this._overlay.style.width = "100%";
        this._overlay.style.top = "0";
        break;

      case "right":
        this._overlay.style.height = "100%";
        this._overlay.style.width = "0";
        this._overlay.style.top = "0";
        this._overlay.style.right = "0";
        break;

      case "left":
        this._overlay.style.height = "100%";
        this._overlay.style.width = "0";
        this._overlay.style.top = "0";
        this._overlay.style.left = "0";
        break;

      default:
        // bottom
        this._overlay.style.height = "0";
        this._overlay.style.width = "100%";
        this._overlay.style.bottom = "0";
        break;
    }
  }

  openOverlay() {
    if (!this._Param.overlay) return;

    this._overlay.style.opacity = "1";
    this._overlayContent.style.opacity = "1";

    //    this._transition = true;
    switch (this._Param.overlay.from) {
      case "top":
        this._overlay.style.height = "100%";
        break;
      case "right":
      case "left":
        this._overlay.style.width = "100%";
        break;
      default:
        // bottom
        this._overlay.style.height = "100%";
        break;
    }
  }

  submitForm = async (evt: Event) => {
    evt.preventDefault();
    if (!this._Param.url) return;
    let dataForm = {};
    Array.from(this._element).forEach((el) => {
      if (
        !(el instanceof HTMLTextAreaElement || el instanceof HTMLInputElement)
      )
        return;

      switch (el.name) {
        case "nom":
          dataForm = { ...dataForm, nom: el.value };
          break;
        case "tel":
          dataForm = { ...dataForm, tel: el.value };
          break;
        case "mail":
          dataForm = { ...dataForm, mail: el.value };
          break;
        case "sujet":
          dataForm = { ...dataForm, sujet: el.value };
          break;
        case "message":
          dataForm = { ...dataForm, message: el.value };
          break;
        default:
          break;
      }
    });

    let msg = this._Param.errorMsg;
    this._overlay.style.display = "block";

    const response = await fetch(this._Param.url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json;charset=utf-8",
      },
      body: JSON.stringify(dataForm),
    });

    if (response.ok) {
      const result = await response.json();
      msg = result.msg;
    } else {
      console.error("Contact: Error response -  ", response);
    }

    if (msg) {
      this._overlayTxt.innerHTML = msg;
      this.openOverlay();
    }
  };

  transitionendOverlay = () => {
    if (!this._transition) return;
    this._transition = false;

    if (this._overlayContent.style.opacity === "0") {
      this._overlay.style.opacity = "0";
    }
  }

  handleBack = (evt: Event) => {
    evt.preventDefault();
    this._element.reset();
    this.initOverlay();
    //this._overlay.style.height = "0";
    this._overlay.addEventListener("transitionend", this.handleBackEnd);
  };

  handleBackEnd = () => {
    this._overlay.style.display = "none";
    this._overlay.removeEventListener("transitionend", this.handleBackEnd);
  };

  static async bind(param: ContactParam) {
    const el = document.getElementById(param.idform);
    if (el instanceof HTMLFormElement) {
      return new Contact(el, param);
    }
  }
}

export default Contact;
