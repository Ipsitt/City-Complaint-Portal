import Header from '@/components/Header';
import Footer from '@/components/Footer';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { 
  Target, 
  Users, 
  Globe, 
  TrendingUp,
  Shield,
  Heart,
  Lightbulb,
  Award
} from 'lucide-react';

const About = () => {
  const sdgGoals = [
    {
      number: 11,
      title: 'Sustainable Cities and Communities',
      description: 'Making cities inclusive, safe, resilient and sustainable',
      color: 'bg-orange-100 text-orange-800'
    },
    {
      number: 16,
      title: 'Peace, Justice and Strong Institutions',
      description: 'Promoting peaceful and inclusive societies for sustainable development',
      color: 'bg-blue-100 text-blue-800'
    },
    {
      number: 17,
      title: 'Partnerships for the Goals',
      description: 'Strengthening the means of implementation and revitalizing partnerships',
      color: 'bg-purple-100 text-purple-800'
    }
  ];

  const features = [
    {
      icon: Users,
      title: 'Community-Driven',
      description: 'Empowering citizens to actively participate in improving their neighborhoods and communities.',
      color: 'text-primary'
    },
    {
      icon: Shield,
      title: 'Transparent Process',
      description: 'Full visibility into complaint handling with regular updates and clear timelines.',
      color: 'text-secondary'
    },
    {
      icon: TrendingUp,
      title: 'Data-Driven Insights',
      description: 'Analytics help authorities prioritize issues and allocate resources effectively.',
      color: 'text-primary'
    },
    {
      icon: Heart,
      title: 'Social Impact',
      description: 'Creating safer, cleaner, and more livable communities for everyone.',
      color: 'text-secondary'
    }
  ];

  const stats = [
    { label: 'Active Users', value: '15,000+', icon: Users },
    { label: 'Issues Resolved', value: '8,500+', icon: Target },
    { label: 'Cities Served', value: '25+', icon: Globe },
    { label: 'Response Rate', value: '85%', icon: TrendingUp }
  ];

  return (
    <div className="min-h-screen bg-background">
      <Header />
      
      <main>
        {/* Hero Section */}
        <section className="bg-gradient-to-r from-primary to-secondary text-primary-foreground py-20">
          <div className="container mx-auto px-4">
            <div className="max-w-4xl mx-auto text-center">
              <h1 className="text-4xl md:text-5xl font-bold mb-6">
                About City Complaint Portal
              </h1>
              <p className="text-xl md:text-2xl text-primary-foreground/90 leading-relaxed">
                We're building stronger communities through collaborative problem-solving 
                and transparent governance, aligned with the United Nations Sustainable Development Goals.
              </p>
            </div>
          </div>
        </section>

        {/* Mission Section */}
        <section className="py-20">
          <div className="container mx-auto px-4">
            <div className="max-w-4xl mx-auto">
              <div className="text-center mb-16">
                <h2 className="text-3xl md:text-4xl font-bold text-foreground mb-4">Our Mission</h2>
                <p className="text-xl text-muted-foreground">
                  Creating a bridge between citizens and local authorities
                </p>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div>
                  <h3 className="text-2xl font-bold mb-6">Empowering Communities</h3>
                  <div className="space-y-4 text-muted-foreground">
                    <p>
                      Our platform serves as a vital communication channel between citizens and local 
                      authorities, ensuring that community concerns are heard, documented, and addressed 
                      efficiently.
                    </p>
                    <p>
                      By providing an easy-to-use digital platform, we're democratizing civic 
                      engagement and making it possible for every citizen to contribute to the 
                      improvement of their community.
                    </p>
                    <p>
                      Through transparency, accountability, and data-driven decision making, we're 
                      helping build more responsive and effective local governance.
                    </p>
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-4">
                  {features.map((feature, index) => (
                    <Card key={index} className="text-center">
                      <CardContent className="pt-6">
                        <feature.icon className={`w-8 h-8 mx-auto mb-3 ${feature.color}`} />
                        <h4 className="font-semibold mb-2">{feature.title}</h4>
                        <p className="text-sm text-muted-foreground">{feature.description}</p>
                      </CardContent>
                    </Card>
                  ))}
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* SDG Section */}
        <section className="py-20 bg-muted/30">
          <div className="container mx-auto px-4">
            <div className="max-w-6xl mx-auto">
              <div className="text-center mb-16">
                <h2 className="text-3xl md:text-4xl font-bold text-foreground mb-4">
                  Aligned with UN Sustainable Development Goals
                </h2>
                <p className="text-xl text-muted-foreground">
                  Contributing to global sustainability through local action
                </p>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                {sdgGoals.map((goal, index) => (
                  <Card key={index} className="text-center">
                    <CardHeader>
                      <div className={`w-16 h-16 rounded-full ${goal.color} mx-auto mb-4 flex items-center justify-center`}>
                        <span className="text-2xl font-bold">#{goal.number}</span>
                      </div>
                      <CardTitle className="text-lg">{goal.title}</CardTitle>
                    </CardHeader>
                    <CardContent>
                      <CardDescription className="text-base">
                        {goal.description}
                      </CardDescription>
                    </CardContent>
                  </Card>
                ))}
              </div>
            </div>
          </div>
        </section>

        {/* Statistics */}
        <section className="py-20">
          <div className="container mx-auto px-4">
            <div className="max-w-4xl mx-auto">
              <div className="text-center mb-16">
                <h2 className="text-3xl md:text-4xl font-bold text-foreground mb-4">
                  Making a Real Impact
                </h2>
                <p className="text-xl text-muted-foreground">
                  See how our community is working together to create positive change
                </p>
              </div>

              <div className="grid grid-cols-2 md:grid-cols-4 gap-8">
                {stats.map((stat, index) => (
                  <div key={index} className="text-center">
                    <div className="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                      <stat.icon className="w-8 h-8 text-primary" />
                    </div>
                    <div className="text-3xl font-bold text-foreground mb-2">{stat.value}</div>
                    <div className="text-sm text-muted-foreground">{stat.label}</div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </section>

        {/* How It Works */}
        <section className="py-20 bg-muted/30">
          <div className="container mx-auto px-4">
            <div className="max-w-4xl mx-auto">
              <div className="text-center mb-16">
                <h2 className="text-3xl md:text-4xl font-bold text-foreground mb-4">
                  How We're Building Better Communities
                </h2>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div className="text-center">
                  <div className="w-16 h-16 bg-primary rounded-full flex items-center justify-center mx-auto mb-6">
                    <span className="text-2xl font-bold text-primary-foreground">1</span>
                  </div>
                  <h3 className="text-xl font-semibold mb-4">Citizens Report</h3>
                  <p className="text-muted-foreground">
                    Community members identify and report issues affecting their neighborhood safety and quality of life.
                  </p>
                </div>

                <div className="text-center">
                  <div className="w-16 h-16 bg-secondary rounded-full flex items-center justify-center mx-auto mb-6">
                    <span className="text-2xl font-bold text-secondary-foreground">2</span>
                  </div>
                  <h3 className="text-xl font-semibold mb-4">Authorities Respond</h3>
                  <p className="text-muted-foreground">
                    Local government departments receive notifications and begin investigation and resolution processes.
                  </p>
                </div>

                <div className="text-center">
                  <div className="w-16 h-16 bg-primary rounded-full flex items-center justify-center mx-auto mb-6">
                    <span className="text-2xl font-bold text-primary-foreground">3</span>
                  </div>
                  <h3 className="text-xl font-semibold mb-4">Communities Improve</h3>
                  <p className="text-muted-foreground">
                    Issues are resolved, communities become safer and more livable, and civic engagement increases.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </section>
      </main>

      <Footer />
    </div>
  );
};

export default About;