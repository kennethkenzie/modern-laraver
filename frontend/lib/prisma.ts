import { PrismaClient } from "@prisma/client";
import { PrismaD1 } from "@prisma/adapter-d1";
import { getCloudflareContext } from "@opennextjs/cloudflare";

let cachedDb: D1Database | undefined;
let cachedClient: PrismaClient | undefined;

function getPrismaClient(): PrismaClient {
  const { env } = getCloudflareContext({ async: false });
  if (env.DB !== cachedDb || !cachedClient) {
    cachedDb = env.DB;
    cachedClient = new PrismaClient({
      adapter: new PrismaD1(env.DB),
      log: ["error", "warn"],
    });
  }
  return cachedClient;
}

export const prisma = new Proxy({} as PrismaClient, {
  get(_target, prop, receiver) {
    const client = getPrismaClient();
    const value = Reflect.get(client, prop, receiver);
    return typeof value === "function" ? value.bind(client) : value;
  },
});

export function resetPrismaConnection() {
  cachedDb = undefined;
  cachedClient = undefined;
}

export function isRetryablePrismaConnectionError(_error: unknown): boolean {
  return false;
}
