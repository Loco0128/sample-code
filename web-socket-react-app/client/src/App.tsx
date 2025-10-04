import { useEffect, useMemo, useRef, useState } from "react";

// ===============================
// 型定義：チャットメッセージの1件分
// ===============================
type ChatMsg = {
  id: string;   // 一意のID
  text: string; // メッセージ本文
  ts: number;   // タイムスタンプ（受信時刻）
};

// ===============================
// メインコンポーネント
// ===============================
export default function App() {
  // メッセージ一覧（受信したメッセージを配列で保持）
  const [messages, setMessages] = useState<ChatMsg[]>([]);

  // 入力欄のテキストを保持
  const [input, setInput] = useState("");

  // WebSocketインスタンスを保持するためのRef（再レンダーされても値が消えない）
  const socketRef = useRef<WebSocket | null>(null);

  // ===============================
  // WebSocket 接続処理（初回マウント時のみ実行）
  // ===============================
  useEffect(() => {
    // サーバーに接続
    const ws = new WebSocket("ws://localhost:8080");
    socketRef.current = ws; // Refに保存して他関数でも使えるようにする

    // 接続成功時
    ws.addEventListener("open", () => {
      console.log("connected");
    });

    // サーバーからメッセージを受信したとき
    ws.addEventListener("message", (event) => {
      const text = String(event.data ?? "");
      setMessages((prev) => [
        ...prev,
        { id: crypto.randomUUID(), text, ts: Date.now() }, // 新しいメッセージを配列に追加
      ]);
    });

    // 接続が閉じられたとき
    ws.addEventListener("close", () => {
      console.log("closed");
    });

    // 通信エラー発生時
    ws.addEventListener("error", (err) => {
      console.error("ws error:", err);
    });

    // クリーンアップ（アンマウント時に接続を閉じる）
    return () => {
      ws.close();
    };
  }, []); // ← [] により初回1回だけ実行される

  // ===============================
  // メッセージ送信可能かどうかの判定（入力が空でないとき）
  // ===============================
  const canSend = useMemo(() => input.trim().length > 0, [input]);

  // ===============================
  // 送信処理（ボタンまたはEnterキー押下時）
  // ===============================
  const handleSend = () => {
    const msg = input.trim();
    if (!msg) return;
    socketRef.current?.send(msg); // WebSocket経由でサーバーに送信
    setInput(""); // 入力欄をリセット
  };

  // Enterキーで送信するショートカット
  const handleKeyDown: React.KeyboardEventHandler<HTMLInputElement> = (e) => {
    if (e.key === "Enter" && canSend) {
      handleSend();
    }
  };

  // ===============================
  // UI部分（メッセージ一覧・入力欄）
  // ===============================
  return (
    <div style={styles.container}>
      <h1 style={styles.title}>リアルタイム チャット</h1>

      {/* チャットメッセージ一覧 */}
      <div style={styles.chatBox}>
        {messages.map((m) => (
          <div key={m.id} style={styles.message}>
            <span>{m.text}</span>
            <time style={styles.time}>
              {new Date(m.ts).toLocaleTimeString()}
            </time>
          </div>
        ))}
      </div>

      {/* 入力欄と送信ボタン */}
      <div style={styles.inputRow}>
        <input
          style={styles.input}
          placeholder="メッセージを入力..."
          value={input}
          onChange={(e) => setInput(e.target.value)}
          onKeyDown={handleKeyDown}
        />
        <button style={styles.button} onClick={handleSend} disabled={!canSend}>
          送信
        </button>
      </div>

      {/* ヒントメッセージ */}
      <p style={styles.tip}>
        もう一つブラウザ（別ウィンドウ/別タブ）で同じページを開き、どちらかで送信すると
        <b>即座にもう一方にも表示</b>されれば成功です。
      </p>
    </div>
  );
}

// ===============================
// スタイル定義（簡易的なCSS）
// ===============================
const styles: Record<string, React.CSSProperties> = {
  container: { maxWidth: 720, margin: "40px auto", padding: 16, fontFamily: "sans-serif" },
  title: { marginBottom: 16 },
  chatBox: {
    border: "1px solid #ddd",
    borderRadius: 8,
    padding: 12,
    minHeight: 280,
    overflowY: "auto",
    background: "#fafafa",
    marginBottom: 12,
  },
  message: {
    display: "flex",
    alignItems: "baseline",
    justifyContent: "space-between",
    gap: 8,
    padding: "6px 8px",
    background: "white",
    borderRadius: 6,
    border: "1px solid #eee",
    marginBottom: 8,
  },
  time: { color: "#888", fontSize: 12 },
  inputRow: { display: "flex", gap: 8 },
  input: {
    flex: 1,
    padding: "10px 12px",
    borderRadius: 8,
    border: "1px solid #ccc",
    outline: "none",
  },
  button: {
    padding: "10px 16px",
    borderRadius: 8,
    border: "1px solid #ccc",
    background: "#fff",
    cursor: "pointer",
  },
  tip: { color: "#555", marginTop: 8 },
};
