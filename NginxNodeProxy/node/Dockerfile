FROM node:20-alpine

WORKDIR /app

# package.json だけコピー
COPY package.json ./

# 依存関係をインストール（devDependencies は省略）
RUN npm install --omit=dev

# アプリ本体をコピー
COPY app.js ./

EXPOSE 3000
CMD ["node", "app.js"]
