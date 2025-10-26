# Use official Node.js 20 image
FROM node:20-alpine

# Set working directory
WORKDIR /app

# Install Yarn globally
RUN npm install -g yarn

# Copy package files
COPY package.json yarn.lock* ./

# Install dependencies using Yarn
RUN yarn install || true

# Copy application files
COPY . .

# Expose Vite dev server port
EXPOSE 5173

# Start development server
CMD ["yarn", "dev", "--host", "0.0.0.0"]
