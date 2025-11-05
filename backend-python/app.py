from flask import Flask, request, jsonify
from flask_cors import CORS
from flask_sqlalchemy import SQLAlchemy
from flask_bcrypt import Bcrypt
from flask_jwt_extended import JWTManager, create_access_token, jwt_required, get_jwt_identity
from werkzeug.utils import secure_filename
import os
from datetime import datetime, timedelta

app = Flask(__name__)

# Configuration
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///anunturi.db'
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False
app.config['JWT_SECRET_KEY'] = 'schimba-cu-un-secret-sigur-aici'
app.config['UPLOAD_FOLDER'] = 'uploads'
app.config['MAX_CONTENT_LENGTH'] = 5 * 1024 * 1024  # 5MB max file size

# Initialize extensions
db = SQLAlchemy(app)
bcrypt = Bcrypt(app)
jwt = JWTManager(app)
CORS(app)

# Ensure upload folder exists
os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)

# Models
class User(db.Model):
    __tablename__ = 'users'
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(100), nullable=False)
    email = db.Column(db.String(255), unique=True, nullable=False)
    password = db.Column(db.String(255), nullable=False)
    phone = db.Column(db.String(20), nullable=False)
    location_county = db.Column(db.String(100))
    location_city = db.Column(db.String(100))
    avatar = db.Column(db.String(255))
    rating_average = db.Column(db.Float, default=0)
    verified = db.Column(db.Boolean, default=False)
    active = db.Column(db.Boolean, default=True)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    
    ads = db.relationship('Ad', backref='seller', lazy=True)

class Ad(db.Model):
    __tablename__ = 'ads'
    id = db.Column(db.Integer, primary_key=True)
    user_id = db.Column(db.Integer, db.ForeignKey('users.id'), nullable=False)
    title = db.Column(db.String(200), nullable=False)
    description = db.Column(db.Text, nullable=False)
    category = db.Column(db.String(50), nullable=False)
    price = db.Column(db.Float, nullable=False)
    currency = db.Column(db.String(3), default='RON')
    condition = db.Column(db.String(20), nullable=False)
    location_city = db.Column(db.String(100), nullable=False)
    location_county = db.Column(db.String(100), nullable=False)
    contact_name = db.Column(db.String(100), nullable=False)
    contact_phone = db.Column(db.String(20), nullable=False)
    status = db.Column(db.String(20), default='active')
    views = db.Column(db.Integer, default=0)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    expires_at = db.Column(db.DateTime, default=lambda: datetime.utcnow() + timedelta(days=30))

# Create tables
with app.app_context():
    db.create_all()

# Routes
@app.route('/api/health', methods=['GET'])
def health():
    return jsonify({'status': 'OK', 'message': 'Server is running'})

# Auth Routes
@app.route('/api/auth/register', methods=['POST'])
def register():
    try:
        data = request.get_json()
        
        # Check if user exists
        if User.query.filter_by(email=data['email']).first():
            return jsonify({'error': 'Emailul există deja'}), 400
        
        # Hash password
        hashed_password = bcrypt.generate_password_hash(data['password']).decode('utf-8')
        
        # Create user
        user = User(
            name=data['name'],
            email=data['email'],
            password=hashed_password,
            phone=data['phone'],
            location_county=data.get('location', {}).get('county'),
            location_city=data.get('location', {}).get('city')
        )
        
        db.session.add(user)
        db.session.commit()
        
        # Generate token
        access_token = create_access_token(identity=user.id, expires_delta=timedelta(days=7))
        
        return jsonify({
            'message': 'Cont creat cu succes!',
            'token': access_token,
            'user': {
                'id': user.id,
                'name': user.name,
                'email': user.email
            }
        }), 201
        
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/api/auth/login', methods=['POST'])
def login():
    try:
        data = request.get_json()
        
        user = User.query.filter_by(email=data['email']).first()
        
        if not user or not bcrypt.check_password_hash(user.password, data['password']):
            return jsonify({'error': 'Email sau parolă incorectă'}), 401
        
        if not user.active:
            return jsonify({'error': 'Contul este dezactivat'}), 401
        
        access_token = create_access_token(identity=user.id, expires_delta=timedelta(days=7))
        
        return jsonify({
            'message': 'Autentificare reușită!',
            'token': access_token,
            'user': {
                'id': user.id,
                'name': user.name,
                'email': user.email
            }
        })
        
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/api/auth/me', methods=['GET'])
@jwt_required()
def get_me():
    user_id = get_jwt_identity()
    user = User.query.get(user_id)
    
    if not user:
        return jsonify({'error': 'Utilizator negăsit'}), 404
    
    return jsonify({
        'user': {
            'id': user.id,
            'name': user.name,
            'email': user.email,
            'phone': user.phone,
            'location': {
                'county': user.location_county,
                'city': user.location_city
            }
        }
    })

# Ads Routes
@app.route('/api/ads', methods=['GET'])
def get_ads():
    page = request.args.get('page', 1, type=int)
    limit = request.args.get('limit', 20, type=int)
    category = request.args.get('category')
    price_min = request.args.get('priceMin', type=float)
    price_max = request.args.get('priceMax', type=float)
    search = request.args.get('search')
    
    query = Ad.query.filter_by(status='active')
    
    if category:
        query = query.filter_by(category=category)
    if price_min:
        query = query.filter(Ad.price >= price_min)
    if price_max:
        query = query.filter(Ad.price <= price_max)
    if search:
        query = query.filter(
            (Ad.title.contains(search)) | (Ad.description.contains(search))
        )
    
    pagination = query.order_by(Ad.created_at.desc()).paginate(
        page=page, per_page=limit, error_out=False
    )
    
    ads = [{
        'id': ad.id,
        'title': ad.title,
        'description': ad.description[:200],
        'price': ad.price,
        'currency': ad.currency,
        'category': ad.category,
        'location': {
            'city': ad.location_city,
            'county': ad.location_county
        },
        'views': ad.views,
        'created_at': ad.created_at.isoformat()
    } for ad in pagination.items]
    
    return jsonify({
        'ads': ads,
        'pagination': {
            'page': page,
            'limit': limit,
            'total': pagination.total,
            'pages': pagination.pages
        }
    })

@app.route('/api/ads/<int:ad_id>', methods=['GET'])
def get_ad(ad_id):
    ad = Ad.query.get_or_404(ad_id)
    
    # Increment views
    ad.views += 1
    db.session.commit()
    
    return jsonify({
        'ad': {
            'id': ad.id,
            'title': ad.title,
            'description': ad.description,
            'price': ad.price,
            'currency': ad.currency,
            'category': ad.category,
            'condition': ad.condition,
            'location': {
                'city': ad.location_city,
                'county': ad.location_county
            },
            'contact': {
                'name': ad.contact_name,
                'phone': ad.contact_phone
            },
            'seller': {
                'id': ad.seller.id,
                'name': ad.seller.name,
                'rating': ad.seller.rating_average
            },
            'views': ad.views,
            'created_at': ad.created_at.isoformat()
        }
    })

@app.route('/api/ads', methods=['POST'])
@jwt_required()
def create_ad():
    user_id = get_jwt_identity()
    data = request.get_json()
    
    ad = Ad(
        user_id=user_id,
        title=data['title'],
        description=data['description'],
        category=data['category'],
        price=data['price'],
        currency=data.get('currency', 'RON'),
        condition=data['condition'],
        location_city=data['location']['city'],
        location_county=data['location']['county'],
        contact_name=data['contact']['name'],
        contact_phone=data['contact']['phone']
    )
    
    db.session.add(ad)
    db.session.commit()
    
    return jsonify({
        'message': 'Anunț creat cu succes!',
        'ad': {'id': ad.id}
    }), 201

@app.route('/api/ads/<int:ad_id>', methods=['DELETE'])
@jwt_required()
def delete_ad(ad_id):
    user_id = get_jwt_identity()
    ad = Ad.query.get_or_404(ad_id)
    
    if ad.user_id != user_id:
        return jsonify({'error': 'Nu ai permisiunea să ștergi acest anunț'}), 403
    
    ad.status = 'deleted'
    db.session.commit()
    
    return jsonify({'message': 'Anunț șters cu succes!'})

if __name__ == '__main__':
    app.run(debug=True, port=5000)


