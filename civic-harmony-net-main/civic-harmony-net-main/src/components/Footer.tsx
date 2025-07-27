import { Link } from 'react-router-dom';
import { MapPin, Mail, Phone, Globe } from 'lucide-react';

const Footer = () => {
  return (
    <footer className="bg-primary text-primary-foreground">
      <div className="container mx-auto px-4 py-12">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
          {/* Brand */}
          <div className="col-span-1 md:col-span-2">
            <div className="flex items-center space-x-2 mb-4">
              <div className="w-8 h-8 bg-secondary rounded-lg flex items-center justify-center">
                <MapPin className="w-5 h-5 text-secondary-foreground" />
              </div>
              <span className="text-xl font-bold">City Complaint Portal</span>
            </div>
            <p className="text-primary-foreground/80 mb-4 max-w-md">
              Empowering citizens to improve their communities through collaborative 
              problem-solving and transparent governance, aligned with UN Sustainable Development Goals.
            </p>
            <div className="flex space-x-4">
              <div className="flex items-center space-x-2 text-sm">
                <Globe className="w-4 h-4" />
                <span>UN SDG Aligned</span>
              </div>
            </div>
          </div>

          {/* Quick Links */}
          <div>
            <h3 className="font-semibold mb-4">Quick Links</h3>
            <ul className="space-y-2">
              <li>
                <Link to="/" className="text-primary-foreground/80 hover:text-primary-foreground transition-colors">
                  Home
                </Link>
              </li>
              <li>
                <Link to="/report" className="text-primary-foreground/80 hover:text-primary-foreground transition-colors">
                  Report Issue
                </Link>
              </li>
              <li>
                <Link to="/track" className="text-primary-foreground/80 hover:text-primary-foreground transition-colors">
                  Track Complaint
                </Link>
              </li>
              <li>
                <Link to="/about" className="text-primary-foreground/80 hover:text-primary-foreground transition-colors">
                  About Portal
                </Link>
              </li>
            </ul>
          </div>

          {/* Contact */}
          <div>
            <h3 className="font-semibold mb-4">Contact</h3>
            <ul className="space-y-2">
              <li className="flex items-center space-x-2 text-sm text-primary-foreground/80">
                <Mail className="w-4 h-4" />
                <span>support@cityportal.gov</span>
              </li>
              <li className="flex items-center space-x-2 text-sm text-primary-foreground/80">
                <Phone className="w-4 h-4" />
                <span>+1-800-CITY-HELP</span>
              </li>
            </ul>
          </div>
        </div>

        <div className="border-t border-primary-foreground/20 mt-8 pt-8 text-center text-sm text-primary-foreground/60">
          <p>&copy; 2024 City Complaint Portal. All rights reserved. Building sustainable communities together.</p>
        </div>
      </div>
    </footer>
  );
};

export default Footer;