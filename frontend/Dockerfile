# Railway deploy image for the React frontend.
# CRA inlines REACT_APP_* variables at build time, so REACT_APP_API_URL
# (the backend's public Railway URL) must be passed as a build ARG.
FROM node:20 AS builder
WORKDIR /usr/src/app
COPY package*.json ./
RUN npm install
COPY . .
ARG REACT_APP_API_URL
ENV REACT_APP_API_URL=$REACT_APP_API_URL
RUN npm run build

FROM node:20-slim
RUN npm install -g serve
WORKDIR /app
COPY --from=builder /usr/src/app/build ./build
EXPOSE 3000
CMD ["sh", "-c", "serve -s build -l ${PORT:-3000}"]
