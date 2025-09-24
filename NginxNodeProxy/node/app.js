import express from "express";
const app = express();

app.get("/", (_req, res) => {
  res.json({ message: "Hello from Node.js via Nginx reverse proxy!" });
});

app.get("/hello", (_req, res) => {
  res.send("Hello route!");
});

// CORS が必要なら簡易対応（必要なければ削除）
app.use((_req, res, next) => {
  res.setHeader("Access-Control-Allow-Origin", "*");
  next();
});

const port = 3000;
app.listen(port, () => {
  console.log(`API listening on http://0.0.0.0:${port}`);
});
