// Firebase Configuration
// Acest fișier integrează Firebase în frontend-ul tău existent

import { initializeApp } from 'firebase/app';
import { getAuth, createUserWithEmailAndPassword, signInWithEmailAndPassword, onAuthStateChanged } from 'firebase/auth';
import { getFirestore, collection, addDoc, getDocs, getDoc, doc, query, where, orderBy, limit, updateDoc, deleteDoc } from 'firebase/firestore';
import { getStorage, ref, uploadBytes, getDownloadURL } from 'firebase/storage';

// Firebase configuration
// IMPORTANT: Înlocuiește cu configurația ta de la Firebase Console!
const firebaseConfig = {
    apiKey: "YOUR_API_KEY_HERE",
    authDomain: "your-project.firebaseapp.com",
    projectId: "your-project-id",
    storageBucket: "your-project.appspot.com",
    messagingSenderId: "123456789",
    appId: "1:123456789:web:abcdefg"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const db = getFirestore(app);
const storage = getStorage(app);

// ============================================
// AUTHENTICATION FUNCTIONS
// ============================================

// Register new user
export async function registerUser(name, email, password, phone) {
    try {
        // Create auth user
        const userCredential = await createUserWithEmailAndPassword(auth, email, password);
        const user = userCredential.user;

        // Add user details to Firestore
        await addDoc(collection(db, 'users'), {
            uid: user.uid,
            name: name,
            email: email,
            phone: phone,
            createdAt: new Date(),
            verified: false,
            active: true
        });

        return {
            success: true,
            user: user,
            message: 'Cont creat cu succes!'
        };
    } catch (error) {
        return {
            success: false,
            error: error.message
        };
    }
}

// Login user
export async function loginUser(email, password) {
    try {
        const userCredential = await signInWithEmailAndPassword(auth, email, password);
        return {
            success: true,
            user: userCredential.user,
            message: 'Autentificare reușită!'
        };
    } catch (error) {
        return {
            success: false,
            error: error.message
        };
    }
}

// Get current user
export function getCurrentUser() {
    return new Promise((resolve, reject) => {
        const unsubscribe = onAuthStateChanged(auth, (user) => {
            unsubscribe();
            resolve(user);
        }, reject);
    });
}

// Logout
export async function logoutUser() {
    try {
        await auth.signOut();
        return { success: true, message: 'Deconectare reușită!' };
    } catch (error) {
        return { success: false, error: error.message };
    }
}

// ============================================
// ADS FUNCTIONS
// ============================================

// Create new ad
export async function createAd(adData) {
    try {
        const user = auth.currentUser;
        if (!user) throw new Error('Trebuie să fii autentificat');

        const docRef = await addDoc(collection(db, 'ads'), {
            ...adData,
            userId: user.uid,
            status: 'active',
            views: 0,
            favorites: 0,
            createdAt: new Date(),
            expiresAt: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000) // 30 days
        });

        return {
            success: true,
            adId: docRef.id,
            message: 'Anunț publicat cu succes!'
        };
    } catch (error) {
        return { success: false, error: error.message };
    }
}

// Get all ads (with filters)
export async function getAds(filters = {}) {
    try {
        let q = query(collection(db, 'ads'), where('status', '==', 'active'));

        if (filters.category) {
            q = query(q, where('category', '==', filters.category));
        }

        if (filters.priceMin) {
            q = query(q, where('price.amount', '>=', parseFloat(filters.priceMin)));
        }

        if (filters.priceMax) {
            q = query(q, where('price.amount', '<=', parseFloat(filters.priceMax)));
        }

        q = query(q, orderBy('createdAt', 'desc'));

        if (filters.limit) {
            q = query(q, limit(parseInt(filters.limit)));
        }

        const querySnapshot = await getDocs(q);
        const ads = [];

        querySnapshot.forEach((doc) => {
            ads.push({
                id: doc.id,
                ...doc.data()
            });
        });

        return { success: true, ads: ads };
    } catch (error) {
        return { success: false, error: error.message };
    }
}

// Get single ad
export async function getAd(adId) {
    try {
        const docRef = doc(db, 'ads', adId);
        const docSnap = await getDoc(docRef);

        if (!docSnap.exists()) {
            return { success: false, error: 'Anunț negăsit' };
        }

        // Increment views
        await updateDoc(docRef, {
            views: (docSnap.data().views || 0) + 1
        });

        return {
            success: true,
            ad: { id: docSnap.id, ...docSnap.data() }
        };
    } catch (error) {
        return { success: false, error: error.message };
    }
}

// Update ad
export async function updateAd(adId, updates) {
    try {
        const user = auth.currentUser;
        if (!user) throw new Error('Trebuie să fii autentificat');

        const docRef = doc(db, 'ads', adId);
        await updateDoc(docRef, updates);

        return { success: true, message: 'Anunț actualizat cu succes!' };
    } catch (error) {
        return { success: false, error: error.message };
    }
}

// Delete ad
export async function deleteAd(adId) {
    try {
        const user = auth.currentUser;
        if (!user) throw new Error('Trebuie să fii autentificat');

        const docRef = doc(db, 'ads', adId);
        await updateDoc(docRef, { status: 'deleted' });

        return { success: true, message: 'Anunț șters cu succes!' };
    } catch (error) {
        return { success: false, error: error.message };
    }
}

// ============================================
// IMAGE UPLOAD FUNCTIONS
// ============================================

// Upload ad images
export async function uploadAdImages(adId, files) {
    try {
        const user = auth.currentUser;
        if (!user) throw new Error('Trebuie să fii autentificat');

        const uploadPromises = [];

        for (let i = 0; i < files.length && i < 10; i++) {
            const file = files[i];
            const storageRef = ref(storage, `ads/${adId}/${Date.now()}-${file.name}`);
            
            uploadPromises.push(
                uploadBytes(storageRef, file).then((snapshot) => {
                    return getDownloadURL(snapshot.ref);
                })
            );
        }

        const urls = await Promise.all(uploadPromises);

        return { success: true, urls: urls };
    } catch (error) {
        return { success: false, error: error.message };
    }
}

// ============================================
// MESSAGES FUNCTIONS
// ============================================

// Send message
export async function sendMessage(receiverId, adId, content) {
    try {
        const user = auth.currentUser;
        if (!user) throw new Error('Trebuie să fii autentificat');

        await addDoc(collection(db, 'messages'), {
            senderId: user.uid,
            receiverId: receiverId,
            adId: adId,
            content: content,
            read: false,
            createdAt: new Date()
        });

        return { success: true, message: 'Mesaj trimis cu succes!' };
    } catch (error) {
        return { success: false, error: error.message };
    }
}

// Get user messages
export async function getMessages(userId) {
    try {
        const q = query(
            collection(db, 'messages'),
            where('receiverId', '==', userId),
            orderBy('createdAt', 'desc')
        );

        const querySnapshot = await getDocs(q);
        const messages = [];

        querySnapshot.forEach((doc) => {
            messages.push({
                id: doc.id,
                ...doc.data()
            });
        });

        return { success: true, messages: messages };
    } catch (error) {
        return { success: false, error: error.message };
    }
}

// Export auth for use in other files
export { auth, db, storage };


