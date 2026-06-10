const esbuild = require('esbuild');

const tiptapSharedPlugin = {
    name: 'tiptap-shared',
    setup(build) {
        const keys = {
            '@tiptap/core': 'core',
            '@tiptap/pm/state': 'pmState',
            '@tiptap/pm/view': 'pmView',
            '@tiptap/pm/model': 'pmModel',
        }

        build.onResolve({ filter: /^@tiptap\/(core|pm\/(state|view|model))$/ }, (args) => ({
            path: args.path,
            namespace: 'tiptap-shared',
        }))

        build.onLoad({ filter: /.*/, namespace: 'tiptap-shared' }, async (args) => {
            const realModule = require(args.path)
            const namedExports = Object.keys(realModule).filter(
                (key) => key !== '__esModule' && key !== 'default',
            )

            const key = keys[args.path]
            let code = `const __module = window.FilamentRichEditor?.tiptap?.${key} ?? window.FilamentRichEditor?.${key};\n`

            if (namedExports.length) {
                code += `export const { ${namedExports.join(', ')} } = __module;\n`
            }

            code += `export default __module?.default ?? __module;\n`

            return { contents: code, loader: 'js' }
        })
    },
}

const sharedOptions = {
    define: {
        'process.env.NODE_ENV': `'production'`,
    },
    bundle: true,
    mainFields: ['module', 'main'],
    platform: 'neutral',
    sourcemap: false,
    sourcesContent: false,
    treeShaking: true,
    target: ['es2020'],
    minify: true,
    plugins: [tiptapSharedPlugin],
}

Promise.all([
    esbuild.build({
        ...sharedOptions,
        entryPoints: ['./resources/js/filament/rich-media-plugins/link-button.js'],
        outfile: './resources/dist/js/filament/rich-media-plugins/link-button.js',
    }),
    esbuild.build({
        ...sharedOptions,
        entryPoints: ['./resources/js/filament/rich-media-plugins/image.js'],
        outfile: './resources/dist/js/filament/rich-media-plugins/image.js',
    }),
]).catch(() => process.exit(1));
