module.exports = {
    'env' : {
        'browser' : true
    },
    'globals' : {
        require : true,
        define : true
    },
    'plugins' : [
        'jsdoc'
    ],
    'rules' : {
        // Possible Errors
        'comma-dangle'             : [2, 'never'],
        'no-cond-assign'           : 2,
        'no-console'               : 0,
        'no-constant-condition'    : 2,
        'no-control-regex'         : 2,
        'no-debugger'              : 2,
        'no-dupe-args'             : 2,
        'no-dupe-keys'             : 2,
        'no-duplicate-case'        : 2,
        'no-empty-character-class' : 2,
        'no-empty'                 : 2,
        'no-ex-assign'             : 2,
        'no-extra-boolean-cast'    : 2,
        'no-extra-parens'          : 2,
        'no-extra-semi'            : 2,
        'no-func-assign'           : 2,
        'no-inner-declarations'    : [2, 'functions'],
        'no-invalid-regexp'        : 2,
        'no-irregular-whitespace'  : 2,
        'no-negated-in-lhs'        : 2,
        'no-obj-calls'             : 2,
        'no-regex-spaces'          : 2,
        'no-sparse-arrays'         : 2,
        'no-unexpected-multiline'  : 2,
        'no-unreachable'           : 2,
        'use-isnan'                : 2,
        'valid-jsdoc'              : [2, {
            'requireReturn' : false,
            'prefer'        : {
                'return' : 'returns'
            }
        }],
        'valid-typeof' : 2,

        // Best Practices
        'accessor-pairs'        : 0,
        'block-scoped-var'      : 2,
        'consistent-return'     : 2,
        'curly'                 : [2, 'all'],
        'default-case'          : 1,
        'dot-location'          : [2, 'property'],
        'dot-notation'          : 1,
        'eqeqeq'                : 2,
        'guard-for-in'          : 2,
        'no-caller'             : 2,
        'no-case-declarations'  : 2,
        'no-div-regex'          : 2,
        'no-else-return'        : 2,
        'no-empty-label'        : 0,
        'no-empty-pattern'      : 2,
        'no-eq-null'            : 2,
        'no-eval'               : 2,
        'no-extend-native'      : 2,
        'no-extra-bind'         : 2,
        'no-fallthrough'        : 2,
        'no-floating-decimal'   : 2,
        'no-implicit-coercion'  : 2,
        'no-implied-eval'       : 2,
        'no-iterator'           : 2,
        'no-labels'             : 2,
        'no-lone-blocks'        : 2,
        'no-loop-func'          : 2,
        'no-magic-numbers'      : [1, {ignore : [-1, 0, 1, 2]}],
        'no-multi-spaces'       : [2, {exceptions : {'VariableDeclarator' : true}}],
        'no-multi-str'          : 0,
        'no-native-reassign'    : 2,
        'no-new-func'           : 2,
        'no-new-wrappers'       : 2,
        'no-new'                : 2,
        'no-octal-escape'       : 2,
        'no-octal'              : 2,
        'no-param-reassign'     : 1,
        'no-process-env'        : 0,
        'no-proto'              : 2,
        'no-redeclare'          : 2,
        'no-return-assign'      : 2,
        'no-script-url'         : 2,
        'no-self-compare'       : 2,
        'no-sequences'          : 2,
        'no-throw-literal'      : 2,
        'no-unused-expressions' : 2,
        'no-useless-concat'     : 2,
        'no-void'               : 2,
        'no-warning-comments'   : 0,
        'no-with'               : 2,
        'radix'                 : 0,
        'vars-on-top'           : 0,
        'wrap-iife'             : [2, 'inside'],
        'yoda'                  : 2,

        // Strict Mode
        strict : [2, 'global'],

        // Variables
        'init-declarations'          : 0,
        'no-catch-shadow'            : 0,
        'no-delete-var'              : 2,
        'no-label-var'               : 0,
        'no-shadow-restricted-names' : 2,
        'no-shadow'                  : 0, // a etudier
        'no-undef-init'              : 2,
        'no-undef'                   : 2,
        'no-undefined'               : 0,
        'no-unused-vars'             : [2, {'vars' : 'all', 'args' : 'after-used'}],
        'no-use-before-define'       : 2,

        // Stylistic Issues
        'array-bracket-spacing'     : [2, 'never'],
        'block-spacing'             : [2, 'always'],
        'brace-style'               : [2, 'stroustrup'],
        'camelcase'                 : 2,
        'comma-spacing'             : [2, {'before' : false, 'after' : true}],
        'comma-style'               : [2, 'last'],
        'computed-property-spacing' : [2, 'never'],
        'consistent-this'           : [2, 'self'],
        'eol-last'                  : 0,
        'func-names'                : 0,
        // 'func-style'                : [2, 'declaration'],
        'id-match'                  : 0,
        'indent'                    : [2, 4, {SwitchCase : 1}],
        'jsx-quotes'                : 0,
        // 'key-spacing'               : [2, {'align' : 'colon', 'beforeColon' : true, 'afterColon' : true}],
        'linebreak-style'           : 0,
        'lines-around-comment'      : 0,
        'max-depth'                 : 0,
        'max-len'                   : [2, 120, 4],
        'max-nested-callbacks'      : [2, 3],
        'max-statements'            : 0,
        'new-cap'                   : 2,
        'new-parens'                : 2,
        'newline-after-var'         : 2,
        'no-array-constructor'      : 2,
        'no-bitwise'                : 2,
        'no-continue'               : 2,
        'no-inline-comments'        : 2,
        'no-lonely-if'              : 2,
        'no-mixed-spaces-and-tabs'  : 2,
        'no-multiple-empty-lines'   : 0,
        'no-negated-condition'      : 0,
        'no-nested-ternary'         : 2,
        'no-new-object'             : 0,
        'no-plusplus'               : 0,
        'no-restricted-syntax'      : 0,
        'no-spaced-func'            : 2,
        'no-ternary'                : 0,
        'no-trailing-spaces'        : 2,
        'no-underscore-dangle'      : [2, {'allow' : ['_headers', 'super_']}],
        'no-unneeded-ternary'       : 2,
        'object-curly-spacing'      : [2, 'never'],
        'one-var'                   : 0,
        'operator-assignment'       : 0, // voir ce que fait google
        'operator-linebreak'        : 0, // voir ce que fait google
        'padded-blocks'             : [2, 'never'],
        'quote-props'               : [2, 'as-needed'],
        'quotes'                    : [2, 'single'],
        'require-jsdoc'             : [2, {
            'require' : {
                'FunctionDeclaration' : true,
                'MethodDefinition'    : true,
                'ClassDeclaration'    : true
            }
        }],
        'semi-spacing'                : [2, {'before' : false, 'after' : true}],
        'semi'                        : [2, 'always'],
        'sort-vars'                   : 0,
        'space-before-blocks'         : 2,
        'space-before-function-paren' : [2, 'never'], // voir ce que fait google
        'space-in-parens'             : [2, 'never'],
        'space-infix-ops'             : 2,
        'space-unary-ops'             : [1, {'words' : true, 'nonwords' : false}],
        'spaced-comment'              : 0,
        'wrap-regex'                  : 2,

        // ECMAScript 6

        // Plugin JSDoc
        'jsdoc/check-param-names'                     : 0,
        'jsdoc/check-types'                           : 2,
        'jsdoc/newline-after-description'             : 0,
        'jsdoc/require-description-complete-sentence' : 0,
        'jsdoc/require-param'                         : 0,
        'jsdoc/require-param-description'             : 0,
        'jsdoc/require-param-types'                   : 0,
        'jsdoc/require-returns-description'           : 0,
        'jsdoc/require-returns-type'                  : 2
    }
};