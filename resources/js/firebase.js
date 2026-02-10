import { initializeApp } from 'firebase/app';
import { getAuth, RecaptchaVerifier, signInWithPhoneNumber } from 'firebase/auth';

const firebaseConfig = {
    apiKey: import.meta.env.VITE_FIREBASE_API_KEY,
    authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
    projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID,
    storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET,
    messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID,
    appId: import.meta.env.VITE_FIREBASE_APP_ID,
    measurementId: import.meta.env.VITE_FIREBASE_MEASUREMENT_ID,
};

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
auth.languageCode = 'th';

let recaptchaVerifier = null;
let confirmationResult = null;

/**
 * Initialize invisible reCAPTCHA on the given button element.
 */
export function setupRecaptcha(buttonId) {
    if (recaptchaVerifier) {
        recaptchaVerifier.clear();
    }

    recaptchaVerifier = new RecaptchaVerifier(auth, buttonId, {
        size: 'invisible',
        callback: () => {},
    });

    return recaptchaVerifier;
}

/**
 * Send OTP to the given phone number.
 * Phone must be in E.164 format, e.g., +66812345678
 */
export async function sendOtp(phoneNumber) {
    if (!recaptchaVerifier) {
        throw new Error('reCAPTCHA not initialized. Call setupRecaptcha first.');
    }

    // Convert Thai format: 0812345678 -> +66812345678
    const e164Phone = phoneNumber.startsWith('0')
        ? '+66' + phoneNumber.substring(1)
        : phoneNumber.startsWith('+')
            ? phoneNumber
            : '+66' + phoneNumber;

    confirmationResult = await signInWithPhoneNumber(auth, e164Phone, recaptchaVerifier);
    return confirmationResult;
}

/**
 * Verify the OTP code entered by the user.
 * Returns the Firebase ID token on success.
 */
export async function verifyOtp(otpCode) {
    if (!confirmationResult) {
        throw new Error('No OTP was sent. Call sendOtp first.');
    }

    const result = await confirmationResult.confirm(otpCode);
    const idToken = await result.user.getIdToken();
    return idToken;
}

/**
 * Send the Firebase ID token to Laravel backend for verification.
 */
export async function loginWithToken(idToken) {
    const response = await fetch('/api/auth/verify-otp', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        },
        body: JSON.stringify({ id_token: idToken }),
    });

    return response.json();
}

export { auth };
