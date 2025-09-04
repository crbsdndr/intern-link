export default {
  content: [
    "./resources/views/**/*.blade.php",
    "./resources/js/**/*.js",
    "./resources/js/**/*.ts",
    "./resources/**/*.vue",
    "./app/View/**/*.php",
  ],
  safelist: [
    {
      pattern: /(bg-(red|green|blue|gray)-(500|600))/, 
    },
    {
      pattern: /(text-(red|green|blue)-(600|700))/, 
    },
    {
      pattern: /(hidden|block|flex|grid|col-span-\d+|row-span-\d+)/,
    },
  ],
  theme: {
    extend: {},
  },
  plugins: [],
};
