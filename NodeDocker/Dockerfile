# ベースイメージを指定（Node.js公式イメージを利用）
FROM node:18

# 作業ディレクトリを作成
WORKDIR /usr/src/app

# 依存ファイルをコピーしてインストール
COPY package*.json ./
RUN npm install

# アプリ本体をコピー
COPY . .

# コンテナがリッスンするポート番号を指定
EXPOSE 3000

# コンテナ起動時に実行するコマンド
CMD ["node", "app.js"]
