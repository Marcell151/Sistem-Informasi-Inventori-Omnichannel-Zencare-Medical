// This is just a conceptual check. In browsers, CRLF from a scanner usually fires two keydown events: 
// one for 'Enter' (CR) and one for 'Enter' (LF) depending on OS/scanner settings.
console.log('Barcode scanners sending CRLF can definitely trigger double Enter.');
