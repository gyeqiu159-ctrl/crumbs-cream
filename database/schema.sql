-- PostgreSQL Schema for Supabase

CREATE TABLE orders (
  id SERIAL PRIMARY KEY,
  customer_name VARCHAR(120) NOT NULL,
  contact_info VARCHAR(150) NOT NULL,
  size VARCHAR(40) NOT NULL,
  flavor VARCHAR(50),
  quantity SMALLINT NOT NULL DEFAULT 1,
  message TEXT,
  amount DECIMAL(10,2),
  status VARCHAR(20) NOT NULL DEFAULT 'new',
  payment_status VARCHAR(20) NOT NULL DEFAULT 'unpaid',
  payment_intent_id VARCHAR(60),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_orders_created_at ON orders(created_at);

CREATE TABLE reviews (
  id SERIAL PRIMARY KEY,
  reviewer_name VARCHAR(120) NOT NULL,
  content TEXT NOT NULL,
  rating SMALLINT NOT NULL DEFAULT 5,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
