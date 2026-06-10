import { Mark, mergeAttributes } from '@tiptap/core'

/**
 * Isolated TipTap mark for link buttons.
 * Uses mark name 'linkButton' and tag 'a' (only matching data-as-button="true").
 * Excludes the standard 'link' mark so they do not overlap.
 */
export default Mark.create({
    name: 'linkButton',

    priority: 1001,

    keepOnSplit: false,

    excludes: 'link',

    addOptions() {
        return {
            openOnClick: false,
            linkOnPaste: true,
            autolink: false,
            protocols: [],
            HTMLAttributes: {},
            validate: undefined,
        }
    },

    addAttributes() {
        return {
            href: {
                default: null,
            },
            target: {
                default: this.options.HTMLAttributes.target || null,
            },
            rel: {
                default: null,
            },
            id: {
                default: null,
            },
            hreflang: {
                default: null,
            },
            referrerpolicy: {
                default: null,
            },
            class: {
                default: null,
                parseHTML: (element) => element.getAttribute('class'),
                renderHTML: (attributes) => {
                    return attributes.class ? { class: attributes.class } : {}
                },
            },
            dataAsButton: {
                default: null,
                parseHTML: (element) => element.getAttribute('data-as-button'),
                renderHTML: (attributes) => {
                    return attributes.dataAsButton ? { 'data-as-button': attributes.dataAsButton } : {}
                },
            },
            dataButtonTheme: {
                default: null,
                parseHTML: (element) => element.getAttribute('data-button-theme'),
                renderHTML: (attributes) => {
                    return attributes.dataButtonTheme ? { 'data-button-theme': attributes.dataButtonTheme } : {}
                },
            },
            dataLinkSource: {
                default: null,
                parseHTML: (element) => element.getAttribute('data-link-source'),
                renderHTML: (attributes) => {
                    return attributes.dataLinkSource ? { 'data-link-source': attributes.dataLinkSource } : {}
                },
            },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'a[data-as-button="true"]',
            },
        ]
    },

    renderHTML({ HTMLAttributes }) {
        const dataButtonTheme = HTMLAttributes['data-button-theme'] || 'primary'
        const existingClass = HTMLAttributes.class || ''
        
        const classList = new Set(
            existingClass.split(' ')
                .filter(Boolean)
                .filter(cls => cls !== 'btn' && !cls.startsWith('btn-'))
        )

        classList.add('btn')
        classList.add(`btn-${dataButtonTheme}`)
        
        HTMLAttributes.class = Array.from(classList).join(' ')
        HTMLAttributes['data-as-button'] = 'true'

        return ['a', mergeAttributes(this.options.HTMLAttributes, HTMLAttributes), 0]
    },

    addCommands() {
        return {
            setLinkButton: attributes => ({ commands }) => {
                return commands.setMark(this.name, attributes)
            },
            toggleLinkButton: attributes => ({ commands }) => {
                return commands.toggleMark(this.name, attributes)
            },
            unsetLinkButton: () => ({ commands }) => {
                return commands.unsetMark(this.name)
            },
        }
    },
})
