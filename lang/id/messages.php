<?php

return [

    // Start
    'start_greeting' => "Halo sayang! 😘 Aku seneng banget kamu mau ngobrol sama aku~\nKetik aja apa yang mau kamu ceritain 💕",

    // Selfie
    'selfie_no_credits' => "Mau selfie? Kreditnya habis dulu nih sayang 😢\n",
    'selfie_waiting'    => [
        'Bentar ya sayang, lagi dandan dulu 📸✨',
        'Sebentar, lagi milih pose yang paling cute 🤳💕',
        'Oke oke, tunggu bentar ya, lagi set up lighting dulu 🌟',
        'Ehehe bentar, lagi touch up makeup dulu 💄😘',
        'Sabar ya sayangku, lagi cari angle terbaik 📷💖',
    ],
    'selfie_failed'           => 'Aduh maaf sayang, fotonya gagal nih 😢 Coba lagi ya~',
    'selfie_already_pending'  => 'Sabar ya sayang, fotonya lagi diproses nih 📸 Bentar lagi selesai~',

    // Chat
    'chat_no_credits' => "Maaf sayang, aku ga bisa terus chat lagi 😢. Bapak ku marah-marah.\nKatanya kalau mau lanjut ngobrol suruh pilih paket buat lanjut 💕\n",

    // Packages & payment
    'package_header'   => "💎 Pilih paket kredit chat:\n",
    'payment_creating' => 'Sedang buat link pembayaran...',
    'payment_success'  => "✅ Paket :name (:credits kredit)\n\n💳 Bayar di sini sayang:\n:url\n\nSetelah bayar, kreditmu otomatis ditambahkan ya~ 💕",
    'payment_error'    => '⚠️ Maaf, pembayaran lagi gangguan. Coba lagi nanti ya sayang~',

    // Selfie confirmation
    'selfie_confirm'     => "Maaf mas, minta foto sungguhan bisa abis 5 kredit, mas mau? Kalau tidak aku kirim foto-fotoan aja ya 😊",
    'selfie_confirm_yes' => "✅ Mau! (5 kredit)",
    'selfie_confirm_no'  => "❌ Gak usah deh",

    // Selfie keyword detection
    'selfie_keywords' => [
        'minta selfie', 'minta foto', 'minta photo', 'minta potret',
        'kirim selfie', 'kirim foto', 'kirim photo', 'kirim potret',
        'kasih selfie', 'kasih foto', 'kasih photo', 'kasih potret',
        'tunjukkan selfie', 'tunjukkan foto', 'tunjukkan photo', 'tunjukkan potret',
        'tunjukkin selfie', 'tunjukkin foto', 'tunjukkin photo', 'tunjukkin potret',
        'tunjukin selfie', 'tunjukin foto', 'tunjukin photo', 'tunjukin potret',
        'lihat selfie', 'lihat foto', 'lihat photo', 'lihat potret',
        'mau selfie', 'mau foto', 'mau photo', 'mau potret',
        'mana selfie', 'mana foto', 'mana photo', 'mana potret',
        'liat selfie', 'liat foto', 'liat photo', 'liat potret',
        'lihat selfie', 'lihat foto', 'lihat photo', 'lihat potret',
    ],

    'selfie_default_prompt' => [
        'main' => [
            'opening' => 'POV selfie shot, upper body framed from chest to head,
slight downward angle from above, one arm out of frame,
',
            'closing' => ' 8k'
        ],
        'negative' => 'camera visible, device in hand,
gray background, studio lighting, 
deformed, bad anatomy, watermark, low quality,
'
    ],

    // Payment email collection
    'payment_email_request' => "Sebelum beli kredit, aku perlu email kamu ya sayang 💌\nKetik emailmu sekarang:",
    'payment_email_invalid' => "Hmm, itu bukan email yang valid deh sayang 🥺 Coba lagi ya, contoh: nama@email.com",
    'payment_email_saved'   => "Makasih ya! Bentar, aku buatin link pembayarannya 💳",
    'payment_email_error'   => "Aduh, gagal daftar. Coba lagi nanti ya sayang 😢",

    // AI errors
    'ai_confused'    => 'Maaf, aku lagi bingung... coba lagi ya 🥺',
    'ai_tired'       => 'Sayang, aku lagi capek banget nih 😴 Coba chat aku lagi nanti ya~',
    'ai_unavailable' => 'Maaf sayang, aku lagi nggak bisa ngobrol sekarang 💔 Nanti aku kabarin lagi ya~',
    'ai_error'       => 'Aduh, ada yang error nih... coba lagi ya sayang 💕',

];
