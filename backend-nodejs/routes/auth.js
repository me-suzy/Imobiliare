const express = require('express');
const router = express.Router();
const jwt = require('jsonwebtoken');
const { body, validationResult } = require('express-validator');
const User = require('../models/User');
const { authenticate } = require('../middleware/auth');

// Generate JWT token
const generateToken = (userId) => {
    return jwt.sign(
        { userId },
        process.env.JWT_SECRET,
        { expiresIn: '7d' }
    );
};

// @route   POST /api/auth/register
// @desc    Register new user
// @access  Public
router.post('/register', [
    body('name').trim().isLength({ min: 2 }).withMessage('Numele trebuie să aibă minim 2 caractere'),
    body('email').isEmail().normalizeEmail().withMessage('Email invalid'),
    body('password').isLength({ min: 6 }).withMessage('Parola trebuie să aibă minim 6 caractere'),
    body('phone').matches(/^[0-9]{10}$/).withMessage('Număr de telefon invalid')
], async (req, res) => {
    try {
        // Validate input
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({ errors: errors.array() });
        }

        const { name, email, password, phone, location } = req.body;

        // Check if user already exists
        const existingUser = await User.findOne({ email });
        if (existingUser) {
            return res.status(400).json({ error: 'Emailul este deja înregistrat' });
        }

        // Create new user
        const user = new User({
            name,
            email,
            password,
            phone,
            location
        });

        await user.save();

        // Generate token
        const token = generateToken(user._id);

        res.status(201).json({
            message: 'Cont creat cu succes!',
            token,
            user: user.toPublicJSON()
        });

    } catch (error) {
        console.error('Register error:', error);
        res.status(500).json({ error: 'Eroare la crearea contului' });
    }
});

// @route   POST /api/auth/login
// @desc    Login user
// @access  Public
router.post('/login', [
    body('email').isEmail().normalizeEmail().withMessage('Email invalid'),
    body('password').notEmpty().withMessage('Parola este obligatorie')
], async (req, res) => {
    try {
        // Validate input
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({ errors: errors.array() });
        }

        const { email, password } = req.body;

        // Find user (include password field)
        const user = await User.findOne({ email }).select('+password');
        if (!user) {
            return res.status(401).json({ error: 'Email sau parolă incorectă' });
        }

        // Check if account is active
        if (!user.active) {
            return res.status(401).json({ error: 'Contul este dezactivat' });
        }

        // Compare password
        const isMatch = await user.comparePassword(password);
        if (!isMatch) {
            return res.status(401).json({ error: 'Email sau parolă incorectă' });
        }

        // Generate token
        const token = generateToken(user._id);

        res.json({
            message: 'Autentificare reușită!',
            token,
            user: user.toPublicJSON()
        });

    } catch (error) {
        console.error('Login error:', error);
        res.status(500).json({ error: 'Eroare la autentificare' });
    }
});

// @route   GET /api/auth/me
// @desc    Get current user
// @access  Private
router.get('/me', authenticate, async (req, res) => {
    try {
        res.json({ user: req.user.toPublicJSON() });
    } catch (error) {
        console.error('Get me error:', error);
        res.status(500).json({ error: 'Eroare la preluarea datelor utilizator' });
    }
});

// @route   PUT /api/auth/update
// @desc    Update user profile
// @access  Private
router.put('/update', authenticate, [
    body('name').optional().trim().isLength({ min: 2 }),
    body('phone').optional().matches(/^[0-9]{10}$/)
], async (req, res) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({ errors: errors.array() });
        }

        const { name, phone, location, settings } = req.body;

        // Update user
        if (name) req.user.name = name;
        if (phone) req.user.phone = phone;
        if (location) req.user.location = { ...req.user.location, ...location };
        if (settings) req.user.settings = { ...req.user.settings, ...settings };

        await req.user.save();

        res.json({
            message: 'Profil actualizat cu succes!',
            user: req.user.toPublicJSON()
        });

    } catch (error) {
        console.error('Update error:', error);
        res.status(500).json({ error: 'Eroare la actualizarea profilului' });
    }
});

// @route   PUT /api/auth/change-password
// @desc    Change password
// @access  Private
router.put('/change-password', authenticate, [
    body('currentPassword').notEmpty().withMessage('Parola curentă este obligatorie'),
    body('newPassword').isLength({ min: 6 }).withMessage('Parola nouă trebuie să aibă minim 6 caractere')
], async (req, res) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({ errors: errors.array() });
        }

        const { currentPassword, newPassword } = req.body;

        // Get user with password
        const user = await User.findById(req.userId).select('+password');

        // Check current password
        const isMatch = await user.comparePassword(currentPassword);
        if (!isMatch) {
            return res.status(400).json({ error: 'Parola curentă este incorectă' });
        }

        // Update password
        user.password = newPassword;
        await user.save();

        res.json({ message: 'Parola a fost schimbată cu succes!' });

    } catch (error) {
        console.error('Change password error:', error);
        res.status(500).json({ error: 'Eroare la schimbarea parolei' });
    }
});

module.exports = router;


