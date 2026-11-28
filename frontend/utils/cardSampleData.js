// Example details used to fill a card template's placeholders wherever a
// template is shown as a design (homepage gallery, template pickers, the
// admin card designer). Fictional person at NFCGo; Malaysian formats.
// A customer's own card preview (CardExamples.vue) never uses these: it shows
// the customer's real details, or clear "Your name"-style placeholders.

export const SAMPLE_CARD_DATA = Object.freeze({
  name: "Aisyah Rahman",
  position: "Pengurus Pemasaran",
  company: "NFCGo",
  phone: "+60 12-345 6789",
  email: "aisyah@nfcgo.com",
  website: "nfcgo.com",
  address: "Jalan Sultan Ismail, 50250 Kuala Lumpur",
});

// NFCGo mark on a transparent background (no app-icon tile) stands in for the
// customer's company logo.
export const SAMPLE_LOGO_URL = "/nfcgo-logo-mark.svg";
