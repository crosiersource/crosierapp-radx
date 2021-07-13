module.exports = {
  ignorePatterns: ["vendor/**/*"],
  root: true,
  env: {
    browser: true,
  },
  parserOptions: {
    parser: "babel-eslint",
    sourceType: "module",
  },
  extends: ["airbnb-base", "plugin:vue/vue3-essential", "prettier/vue", "plugin:prettier/recommended"],
  rules: {
    "prefer-destructuring": [
      "error",
      {
        array: false,
        object: false,
      },
      {
        enforceForRenamedProperties: false,
      },
    ],
    "no-param-reassign": "off",
    "no-plusplus": "off",
    "no-console": "off",
    "comma-dangle": "off",
    "class-methods-use-this": "off",
    "import/no-unresolved": "off",
    "import/extensions": "off",
    "implicit-arrow-linebreak": "off",
    "import/prefer-default-export": "off",
    "prettier/prettier": ["error", { singleQuote: false, endOfLine: "auto" }],
    "max-len": ["error", { code: 100, comments: 140 }],
  },
};
