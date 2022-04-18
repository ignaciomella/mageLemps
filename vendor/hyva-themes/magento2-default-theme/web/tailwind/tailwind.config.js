const { spacing } = require('tailwindcss/defaultTheme');
const colors = require('tailwindcss/colors');


module.exports = {
    mode: process.env.TAILWIND_COMPILE_MODE || 'jit', // either 'jit' or 'aot'
    theme: {
        extend: {
            screens: {
                'sm': '640px',
                // => @media (min-width: 640px) { ... }
                'md': '768px',
                // => @media (min-width: 768px) { ... }
                'lg': '1024px',
                // => @media (min-width: 1024px) { ... }
                'xl': '1280px',
                // => @media (min-width: 1280px) { ... }
                '2xl': '1536px',
                // => @media (min-width: 1536px) { ... }
            },
            colors: {
                primary: {
                    lighter: colors.blue['300'],
                    "DEFAULT": colors.blue['800'],
                    darker: colors.blue['900'],
                },
                secondary: {
                    lighter: colors.blue['100'],
                    "DEFAULT": colors.blue['200'],
                    darker: colors.blue['300'],
                },
                background: {
                    lighter: colors.blue['100'],
                    "DEFAULT": colors.blue['200'],
                    darker: colors.blue['300'],
                }
            },
            textColor: {
                orange: colors.orange,
                primary: {
                    lighter: colors.gray['700'],
                    "DEFAULT": colors.gray['800'],
                    darker: colors.gray['900'],
                },
                secondary: {
                    lighter: colors.gray['400'],
                    "DEFAULT": colors.gray['600'],
                    darker: colors.gray['800'],
                },
            },
            backgroundColor: {
                primary: {
                    lighter: colors.blue['600'],
                    "DEFAULT": colors.blue['700'],
                    darker: colors.blue['800'],
                },
                secondary: {
                    lighter: colors.blue['100'],
                    "DEFAULT": colors.blue['200'],
                    darker: colors.blue['300'],
                },
                container: {
                    lighter: '#ffffff',
                    "DEFAULT": '#fafafa',
                    darker: '#f5f5f5',
                }
            },
            borderColor: {
                primary: {
                    lighter: colors.blue['600'],
                    "DEFAULT": colors.blue['700'],
                    darker: colors.blue['800'],
                },
                secondary: {
                    lighter: colors.blue['100'],
                    "DEFAULT": colors.blue['200'],
                    darker: colors.blue['300'],
                },
                container: {
                    lighter: '#f5f5f5',
                    "DEFAULT": '#e7e7e7',
                    darker: '#b6b6b6',
                }
            },
            minWidth: {
                8: spacing["8"],
                20: spacing["20"],
                40: spacing["40"],
                48: spacing["48"],
            },
            minHeight: {
                14: spacing["14"],
                'screen-25': '25vh',
                'screen-50': '50vh',
                'screen-75': '75vh',
            },
            maxHeight: {
                '0': '0',
                'screen-25': '25vh',
                'screen-50': '50vh',
                'screen-75': '75vh',
            },
            container: {
                center: true,
                padding: '1.5rem'
            }
        },
    },
    variants: {
        extend: {
            borderWidth: ['last', 'hover', 'focus'],
            margin: ['last'],
            opacity: ['disabled'],
            backgroundColor: ['even', 'odd'],
            ringWidth: ['active']
        }
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
    purge: {
        // Examples for excluding patterns from purge
        // options: {
        //     safelist: [/^bg-opacity-/, /^-?[mp][trblxy]?-[4,8]$/, /^text-shadow/],
        // },
        content: [
            // this theme's phtml files
            '../../**/*.phtml',
            // hyva theme-module templates (if this is the default theme in vendor/hyva-themes/magento2-default-theme)
            '../../../magento2-theme-module/src/view/frontend/templates/**/*.phtml',
            // hyva theme-module templates (if this is a child theme)
            //'../../../../../../../vendor/hyva-themes/magento2-theme-module/src/view/frontend/templates/**/*.phtml',
            // parent theme in Vendor (if this is a child-theme)
            //'../../../../../../../vendor/hyva-themes/magento2-default-theme/**/*.phtml',
            // app/code phtml files (if need tailwind classes from app/code modules)
            //'../../../../../../../app/code/**/*.phtml',
            // react app src files (if Hyvä Checkout is installed in app/code)
            //'../../../../../../../app/code/**/src/**/*.jsx',
            // react app src files in vendor (If Hyvä Checkout is installed in vendor)
            //'../../../../../../../vendor/hyva-themes/magento2-hyva-checkout/src/reactapp/src/**/*.jsx',
            //'../../../../../../../vendor/hyva-themes/magento2-hyva-checkout/src/view/frontend/templates/react-container.phtml',
            // widget block classes from app/code
            //'../../../../../../../app/code/**/Block/Widget/**/*.php'
        ]
    }
}
