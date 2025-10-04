// wsライブラリから WebSocketServer クラスをインポート
import { WebSocketServer } from "ws";

// WebSocketサーバーを待ち受けるポート番号
const PORT = 8080;

// WebSocketサーバーを作成して指定ポートで起動
const wss = new WebSocketServer({ port: PORT });

/**
 * すべての接続中クライアントにデータを送信する関数
 * （exceptで指定されたクライアント以外に送る）
 */
function broadcast(data, except) {
  for (const client of wss.clients) {
    // readyState === 1 は「接続中（OPEN）」を意味する
    if (client !== except && client.readyState === 1) {
      client.send(data);
    }
  }
}

/**
 * クライアントが接続した時に呼ばれるイベント
 * `ws` は接続してきたクライアント1人分のソケット
 */
wss.on("connection", (ws) => {
  console.log("Client connected");

  /**
   * クライアントからメッセージを受信した時に呼ばれるイベント
   * msgBuf は Buffer 型なので、文字列化して扱う
   */
  ws.on("message", (msgBuf) => {
    const text = msgBuf.toString();
    console.log(`Received: ${text}`);

    // 受信したメッセージを全員に配信（送信者も含めてOK）
    broadcast(text, null);
  });

  /**
   * クライアントが切断した時に呼ばれるイベント
   */
  ws.on("close", () => {
    console.log("Client disconnected");
  });

  /**
   * エラー発生時（例：ネットワーク切断など）
   */
  ws.on("error", (err) => {
    console.error("⚠️ WebSocket error:", err);
  });
});

// サーバー起動ログ
console.log(`WebSocket server listening on ws://localhost:${PORT}`);
