import re

with open('resources/views/user/pages/chatboard/index.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

pattern = re.compile(
    r"(?P<indent> +)if \(typeof window\.Echo !== 'undefined'\) \{\n(?P=indent)    window\.Echo\.private\('conversation\.\{\{ \$activeConversation->id \}\}'\)\n(?P=indent)        \.listen\('\.MessageSent', \(e\) => \{\n(?P=indent)            appendMessageToChat\(e\.message\);\n(?P=indent)        \}\);\n(?P=indent)\}",
    re.DOTALL
)

replacement = """        document.addEventListener('DOMContentLoaded', () => {
            if (typeof window.Echo !== 'undefined') {
                window.Echo.private('conversation.{{ $activeConversation->id }}')
                    .listen('.MessageSent', (e) => {
                        appendMessageToChat(e.message);
                    });
            }
        });"""

new_content = pattern.sub(replacement, content)

if new_content != content:
    with open('resources/views/user/pages/chatboard/index.blade.php', 'w', encoding='utf-8') as f:
        f.write(new_content)
    print("Replaced successfully")
else:
    print("Pattern not found")
