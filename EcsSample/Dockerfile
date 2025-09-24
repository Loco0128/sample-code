FROM node:22-alpine

# セキュリティ：非rootで実行
USER node

WORKDIR /usr/src/app
COPY --chown=node:node package*.json ./
ENV NODE_ENV=production
RUN npm ci --omit=dev || npm install --omit=dev || true

# アプリ本体をコピー
COPY --chown=node:node . .

EXPOSE 3000
CMD ["node", "app.js"]
