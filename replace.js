const fs = require('fs');
let s = fs.readFileSync('resources/views/user/pages/chatboard/index.blade.php', 'utf8');

// Replace PHP link
s = s.replace(
    /<a href="#" class="(mt-2 text-center py-1\.5 px-3 rounded-lg font-bold text-\[11px\] transition-all \{\{ \$isMine \? 'bg-white\/20 hover:bg-white\/30 text-white' : 'bg-sky-500 hover:bg-sky-600 text-white' \}\})">[\s\S]*?<\/a>/,
    '<button type="button" onclick="openQuizRunner({{ $message->form_id }})" class="w-full $1">Bắt đầu làm bài</button>'
);

// Replace JS link
s = s.replace(
    /<a href="#" class="(mt-2 text-center py-1\.5 px-3 rounded-lg font-bold text-\[11px\] transition-all \$\{isMine \? 'bg-white\/20 hover:bg-white\/30 text-white' : 'bg-sky-500 hover:bg-sky-600 text-white'\})">[\s\S]*?<\/a>/,
    '<button type="button" onclick="openQuizRunner(${message.form_id || \'{{ $message->form_id }}\'})" class="w-full $1">Bắt đầu làm bài</button>'
);

fs.writeFileSync('resources/views/user/pages/chatboard/index.blade.php', s);
