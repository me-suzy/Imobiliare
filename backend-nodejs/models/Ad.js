const mongoose = require('mongoose');

const adSchema = new mongoose.Schema({
    title: {
        type: String,
        required: [true, 'Titlul este obligatoriu'],
        trim: true,
        minlength: [10, 'Titlul trebuie să aibă minim 10 caractere'],
        maxlength: [100, 'Titlul poate avea maxim 100 caractere']
    },
    description: {
        type: String,
        required: [true, 'Descrierea este obligatorie'],
        minlength: [20, 'Descrierea trebuie să aibă minim 20 caractere'],
        maxlength: [5000, 'Descrierea poate avea maxim 5000 caractere']
    },
    category: {
        type: String,
        required: [true, 'Categoria este obligatorie'],
        enum: [
            'imobiliare',
            'auto-moto',
            'mobilier-casa',
            'joburi',
            'electronice',
            'animale',
            'fashion',
            'sport',
            'altele'
        ]
    },
    subcategory: {
        type: String,
        default: null
    },
    price: {
        amount: {
            type: Number,
            required: [true, 'Prețul este obligatoriu'],
            min: [0, 'Prețul nu poate fi negativ']
        },
        currency: {
            type: String,
            enum: ['RON', 'EUR', 'USD'],
            default: 'RON'
        },
        negotiable: {
            type: Boolean,
            default: false
        }
    },
    condition: {
        type: String,
        enum: ['nou', 'folosit'],
        required: true
    },
    images: [{
        url: String,
        filename: String,
        uploadedAt: {
            type: Date,
            default: Date.now
        }
    }],
    location: {
        county: {
            type: String,
            required: true
        },
        city: {
            type: String,
            required: true
        },
        address: String
    },
    seller: {
        type: mongoose.Schema.Types.ObjectId,
        ref: 'User',
        required: true
    },
    contact: {
        name: {
            type: String,
            required: true
        },
        phone: {
            type: String,
            required: true
        },
        email: String,
        hidePhone: {
            type: Boolean,
            default: false
        }
    },
    status: {
        type: String,
        enum: ['active', 'pending', 'sold', 'expired', 'deleted'],
        default: 'active'
    },
    promoted: {
        isPromoted: {
            type: Boolean,
            default: false
        },
        promotedUntil: Date,
        promotionType: {
            type: String,
            enum: ['top', 'featured', 'urgent']
        }
    },
    statistics: {
        views: {
            type: Number,
            default: 0
        },
        favorites: {
            type: Number,
            default: 0
        },
        messages: {
            type: Number,
            default: 0
        }
    },
    expiresAt: {
        type: Date,
        default: function() {
            return new Date(Date.now() + 30 * 24 * 60 * 60 * 1000); // 30 days
        }
    }
}, {
    timestamps: true
});

// Indexes for better query performance
adSchema.index({ title: 'text', description: 'text' });
adSchema.index({ category: 1, status: 1 });
adSchema.index({ seller: 1, status: 1 });
adSchema.index({ 'location.city': 1 });
adSchema.index({ createdAt: -1 });
adSchema.index({ 'price.amount': 1 });

// Method to increment views
adSchema.methods.incrementViews = function() {
    this.statistics.views += 1;
    return this.save();
};

// Method to check if ad is expired
adSchema.methods.isExpired = function() {
    return this.expiresAt < new Date();
};

// Virtual for formatted price
adSchema.virtual('formattedPrice').get(function() {
    const formatter = new Intl.NumberFormat('ro-RO', {
        style: 'currency',
        currency: this.price.currency
    });
    return formatter.format(this.price.amount);
});

module.exports = mongoose.model('Ad', adSchema);


