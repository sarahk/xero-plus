// see templateEdit.js for how to use these functions

// SHORTCODES can be exported if you want, but not necessary
const SHORTCODES = [
    {text: 'First name', code: '[first_name]'},
    {text: 'Last name', code: '[last_name]'},
    {text: 'Full name', code: '[full_name]'},
    {text: 'Amount due', code: '[amount_due]'},
    {text: 'Due date', code: '[due_date]'}
];

export const CROWNEMOJIS = {
    custom_crown: {
        keywords: ['crown', 'king', 'queen', 'royal'],
        char: '👑'
    }
};

// Toolbar version: adds a “Shortcodes” menu button to the *toolbar*
export function registerShortcodesToolbar(editor) {
    editor.ui.registry.addMenuButton('shortcodes', {
        text: 'Shortcodes',
        tooltip: 'Insert a shortcode',
        icon: 'bookmark',
        fetch: (cb) => {
            cb(SHORTCODES.map((sc) => ({
                type: 'menuitem',
                text: sc.text,
                onAction: () => editor.insertContent(sc.code)
            })));
        }
    });
}

// Menubar version: registers items; you must also add a `menu` config
export function registerShortcodesMenu(editor) {
    SHORTCODES.forEach((sc) => {
        const key = 'sc_' + sc.code.replace(/\W+/g, '_');
        editor.ui.registry.addMenuItem(key, {
            text: sc.text,
            onAction: () => editor.insertContent(sc.code)
        });
    });
}

// Helper to build the items string for the custom “shortcodes” menu
export function shortcodesMenuItems() {
    return SHORTCODES
        .map((sc) => 'sc_' + sc.code.replace(/\W+/g, '_'))
        .join(' ');
}
