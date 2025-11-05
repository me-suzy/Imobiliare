const mongoose = require('mongoose');
const bcrypt = require('bcryptjs');

const userSchema = new mongoose.Schema({
    name: {
        type: String,
        required: [true, 'Numele este obligatoriu'],
        trim: true,
        minlength: [2, 'Numele trebuie să aibă minim 2 caractere']
    },
    email: {
        type: String,
        required: [true, 'Emailul este obligatoriu'],
        unique: true,
        lowercase: true,
        trim: true,
        match: [/^\S+@\S+\.\S+$/, 'Email invalid']
    },
    password: {
        type: String,
        required: [true, 'Parola este obligatorie'],
        minlength: [6, 'Parola trebuie să aibă minim 6 caractere'],
        select: false // Nu returnează parola în queries by default
    },
    phone: {
        type: String,
        required: [true, 'Telefonul este obligatoriu'],
        match: [/^[0-9]{10}$/, 'Număr de telefon invalid (10 cifre)']
    },
    location: {
        county: String,
        city: String,
        address: String
    },
    avatar: {
        type: String,
        default: null
    },
    rating: {
        average: {
            type: Number,
            default: 0,
            min: 0,
            max: 5
        },
        count: {
            type: Number,
            default: 0
        }
    },
    verified: {
        type: Boolean,
        default: false
    },
    active: {
        type: Boolean,
        default: true
    },
    settings: {
        emailNotifications: {
            type: Boolean,
            default: true
        },
        smsNotifications: {
            type: Boolean,
            default: false
        },
        showPhone: {
            type: Boolean,
            default: true
        },
        showEmail: {
            type: Boolean,
            default: false
        }
    }
}, {
    timestamps: true
});

// Hash password before saving
userSchema.pre('save', async function(next) {
    if (!this.isModified('password')) return next();
    
    try {
        const salt = await bcrypt.genSalt(10);
        this.password = await bcrypt.hash(this.password, salt);
        next();
    } catch (error) {
        next(error);
    }
});

// Method to compare password
userSchema.methods.comparePassword = async function(candidatePassword) {
    return await bcrypt.compare(candidatePassword, this.password);
};

// Method to get public profile
userSchema.methods.toPublicJSON = function() {
    return {
        id: this._id,
        name: this.name,
        email: this.settings.showEmail ? this.email : undefined,
        phone: this.settings.showPhone ? this.phone : undefined,
        location: this.location,
        avatar: this.avatar,
        rating: this.rating,
        verified: this.verified,
        memberSince: this.createdAt
    };
};

module.exports = mongoose.model('User', userSchema);


