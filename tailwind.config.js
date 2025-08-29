module.exports = {
  content: [
    "./resources/views/**/*.blade.php",
    "./app/Http/Controllers/Web/**/*.php",
  ],
  theme: {
    extend: {
      colors: { brand: "#0ea5e9", branddark: "#0b7db3" },
      borderRadius: { xl: "12px" },
    },
  },
  plugins: [],
};
