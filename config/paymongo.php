<?php
/**
 * PayMongo credentials
 * -----------------------------------------------------------------
 * IMPORTANT: this file holds a secret key. Never commit it to a
 * public repo. Add this line to your .gitignore:
 *   config/paymongo.php
 *
 * If this key was ever pasted anywhere outside your own codebase
 * (chat, a support ticket, a public repo, etc.), rotate it:
 *   PayMongo Dashboard -> Developers -> API Keys -> regenerate
 * then update the value below.
 *
 * Get your keys from: https://dashboard.paymongo.com/developers/api-keys
 * Use the "Test" keys while developing, "Live" keys only once you're
 * ready to accept real payments.
 */

define('PAYMONGO_SECRET_KEY', 'sk_test_woycr8FXewQo6CABEkViMjYj');
define('PAYMONGO_CURRENCY', 'PHP');

// How long a QR code stays scannable before it expires, in seconds.
// PayMongo allows 60–9000; default is 1800 (30 minutes).
define('PAYMONGO_QR_EXPIRY_SECONDS', 1800);
