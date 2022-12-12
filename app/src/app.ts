/**
 * Script pour l'application de test
 *
 * 09/01/2022 - Version initiale
 * 13/14/2022 - Utilisaation de TypeScript
 */

import "./modules/Contact";
import { Reveal } from "fski-reveal";
import Contact from "./modules/Contact";

Reveal.bind({ infinite: true }); // Reveal

Contact.bind( {
    idform: "contactForm", 
    btn: {lib: "Ok", class: ["btn", "secondary"]},
    overlay: {from: "right", class: ["form-overlay", "primary"]}
})