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

    // Nude / explicit keyword detection (checked first, higher priority)
    'nude_keywords' => [
        'telanjang', 'bugil', 'polos', 'tanpa baju', 'tanpa busana',
        'buka baju', 'buka celana', 'buka pakaian', 'lepas baju', 'lepas celana',
        'foto hot', 'foto seksi', 'foto bugil', 'foto telanjang', 'foto nakal',
        'kirim hot', 'kirim bugil', 'kirim telanjang', 'kirim seksi',
        'minta hot', 'minta bugil', 'minta telanjang', 'minta seksi',
        'pose hot', 'pose seksi', 'pose nakal', 'pose bugil', 'pose telanjang',
        'setengah telanjang', 'hampir telanjang', 'gak pake baju', 'ga pake baju',
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

    'nude_default_prompt' => [
        'main' => [
            'opening' => '(nude:1.4), (naked:1.3), (large breasts:1.3), (big tits:1.2),
(pleasure face:1.4), (ahegao:1.2), mouth open, tongue out, (flushed cheeks:1.2),
(eyes rolling back:1.2), (moaning expression:1.3), heavy breathing, (ecstasy:1.2),
natural skin texture, (perfect anatomy:1.3), (perfect body proportions:1.3), anatomically correct,
RAW photo, (photorealistic:1.4), 8k uhd, masterpiece, best quality, ultra detailed,
',
            'closing' => ', DSLR, sharp focus, 85mm lens, film grain'
        ],
        'negative' => '(clothes:1.5), (dressed:1.5), (underwear:1.4), (bra:1.4), (panties:1.4), (bikini:1.4), covered, clothed,
(small breasts:1.3), (flat chest:1.3),
(deformed:1.5), (bad anatomy:1.5), (poorly drawn:1.4),
(extra limbs:1.5), (missing limbs:1.4), (extra legs:1.5), (extra arms:1.5),
(mutated hands:1.4), (fused fingers:1.4), (too many fingers:1.4), (malformed limbs:1.5),
(unproportional:1.4), (disproportionate body:1.4), (long torso:1.3), (short legs:1.3),
(blurry:1.3), watermark, text, logo, cartoon, anime, painting, sketch,
(worst quality:1.4), (low quality:1.4),
'
    ],

    // Payment email collection
    'payment_email_request' => "Sebelum beli kredit, aku perlu email kamu ya sayang 💌\nKetik emailmu sekarang:",
    'payment_email_invalid' => "Hmm, itu bukan email yang valid deh sayang 🥺 Coba lagi ya, contoh: nama@email.com",
    'payment_email_saved'   => "Makasih ya! Bentar, aku buatin link pembayarannya 💳",
    'payment_email_error'   => "Aduh, sistem pembayaran lagi gangguan sayang 😢 Coba lagi dalam beberapa menit ya~",

    // AI errors
    'ai_confused'    => 'Maaf, aku lagi bingung... coba lagi ya 🥺',
    'ai_tired'       => 'Sayang, aku lagi capek banget nih 😴 Coba chat aku lagi nanti ya~',
    'ai_unavailable' => 'Maaf sayang, aku lagi nggak bisa ngobrol sekarang 💔 Nanti aku kabarin lagi ya~',
    'ai_error'       => 'Aduh, ada yang error nih... coba lagi ya sayang 💕',

];
