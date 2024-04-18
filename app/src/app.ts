/**
 * Script pour l'application de test
 *
 * 09/01/2022 - Version initiale
 * 13/14/2022 - Utilisation de TypeScript
 */

import "./modules/Contact";
import { Reveal } from "fski-reveal";
import Contact from "./modules/Contact";
import { postMsg, data_msg } from "./modules/apiStaff";

Reveal.bind({ infinite: true }); // Reveal

Contact.bind(
  "contactForm",
  async (form: HTMLFormElement): Promise<string> => {
    const data: data_msg = { nom: "", mail: "", message: "" };
    const mail = form.elements.namedItem("mail");
    const nom = form.elements.namedItem("nom");
    const message = form.elements.namedItem("message");
    const sujet = form.elements.namedItem("sujet");
    if (nom instanceof HTMLInputElement) data.nom = nom.value;
    if (mail instanceof HTMLInputElement) data.mail = mail.value;
    if (sujet instanceof HTMLInputElement) data.sujet = sujet.value;
    if (message instanceof HTMLTextAreaElement) data.message = message.value;
    const answer = await postMsg(data);

    if (answer) return "Merci, Thank you";

    return "Sorry something was wrong, please try later";
  },
  {
    effect: "left",
    className: ["form-overlay", "bg-light"],
    btn: { name: "Ok", class: ["btn", "btn-secondary"] },
  }
);
