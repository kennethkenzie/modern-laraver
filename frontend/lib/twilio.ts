import twilio from "twilio";
import { isValidE164Phone, normalizePhoneNumber } from "@/lib/phone";

const accountSid = process.env.TWILIO_ACCOUNT_SID;
const authToken = process.env.TWILIO_AUTH_TOKEN;
const verifyServiceSid = process.env.TWILIO_VERIFY_SERVICE_SID;

function getTwilioClient() {
  if (!accountSid || !authToken || !verifyServiceSid) {
    throw new Error("Twilio Verify env vars are missing.");
  }

  return {
    client: twilio(accountSid, authToken),
    verifyServiceSid,
  };
}

export async function sendPhoneOtp(phone: string) {
  if (process.env.TWILIO_SKIP_VERIFY === "true") {
    console.log(`[AUTH] Skipping real SMS for ${phone}. Use 123456 to verify.`);
    return { status: "pending" };
  }

  const { client, verifyServiceSid } = getTwilioClient();
  const normalizedPhone = normalizePhoneNumber(phone);

  if (!isValidE164Phone(normalizedPhone)) {
    throw new Error("Phone number must be in international format, for example +256700000000.");
  }

  return client.verify.v2
    .services(verifyServiceSid)
    .verifications.create({
      to: normalizedPhone,
      channel: "sms",
    });
}

export async function verifyPhoneOtp(phone: string, code: string) {
  if (process.env.TWILIO_SKIP_VERIFY === "true" && code === "123456") {
    return { status: "approved", valid: true };
  }

  const { client, verifyServiceSid } = getTwilioClient();
  const normalizedPhone = normalizePhoneNumber(phone);

  if (!isValidE164Phone(normalizedPhone)) {
    throw new Error("Phone number must be in international format, for example +256700000000.");
  }

  const check = await client.verify.v2
    .services(verifyServiceSid)
    .verificationChecks.create({
      to: normalizedPhone,
      code,
    });

  return { status: check.status, valid: check.status === "approved" };
}
