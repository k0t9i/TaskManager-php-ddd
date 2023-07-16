FROM node:lts-alpine

WORKDIR /app

COPY ./src-frontend/package*.json ./

CMD \[ -d "node_modules" \] && npm run dev || npm ci && npm run dev