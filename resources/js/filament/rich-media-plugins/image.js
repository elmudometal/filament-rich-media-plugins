import { Node, mergeAttributes } from '@tiptap/core'

/**
 * Custom Image node for the ImagePlugin.
 * Extends the default TipTap image with lazy loading support.
 * Keeps the node name 'image' to override the built-in image node.
 */
export default Node.create({
    name: 'image',

    group: 'inline',

    inline: true,

    draggable: true,

    addAttributes() {
        return {
            src: {
                default: null,
            },
            alt: {
                default: null,
            },
            title: {
                default: null,
            },
            width: {
                default: null,
            },
            height: {
                default: null,
            },
            id: {
                default: null,
            },
            lazy: {
                default: null,
                parseHTML: (element) => {
                    return element.getAttribute('loading') === 'lazy'
                        ? (element.getAttribute('data-lazy') || 'true')
                        : null
                },
                renderHTML: (attributes) => {
                    if (attributes.lazy) {
                        return {
                            'data-lazy': attributes.lazy,
                            'loading': 'lazy',
                        }
                    }
                    return {}
                },
            },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'img[src]',
            },
        ]
    },

    renderHTML({ HTMLAttributes }) {
        return ['img', mergeAttributes(this.options.HTMLAttributes, HTMLAttributes)]
    },

    addCommands() {
        return {
            setImage: (attributes) => ({ commands }) => {
                return commands.insertContent({
                    type: this.name,
                    attrs: attributes,
                })
            },
        }
    },
})
